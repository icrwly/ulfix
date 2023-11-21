#!/bin/bash

# ###
# Deploys UL Custom Upstream updates to Pantheon multidev environments. Enforces workflow conventions and
# automates various release and deploy tasks.
#   - $DEVELOPMENT_BRANCH will be deployed to the develop environment.
#       Runs updates and imports configuration via Github Actions CI. This is the default branch.
#   - $PRODUCTION_BRANCH will be deployed to dev environment. Live database and files are sync'd to
#       dev environment. Runs updates and imports configuration via Github Actions CI. This is the master branch.
#   - Pull requests to any branch will be deployed to pr-# multidev environment.
#   - PR-# multidevs are deleted when the merge request is closed/merged
#   - Push back comments with a link to the target site and environment site on Pantheon
#       are posted to each pull request.
# ###

# Exit if anything fails
set -o pipefail

set -x


# Cut gitignore at the cut mark.
terminus build:gitignore:cut

# Authenticate with Terminus
terminus -n auth:login --machine-token="$TERMINUS_TOKEN"

# Env to create multidevs from which environment
MDS_FROM="dev"

# Git config to support advanced feature branching, PR workflows, and automated conflict resolution.
git config --global --add safe.directory /__w/ulcutesting/ulcutesting

git config --local core.excludesfile false
git config --local core.fileMode true
git config --global pull.rebase false

# Override default workflow:wait timeout of 180 seconds via terminus robo config
cp .ci/deploy/pantheon/config.yml ~/.terminus

# Setup SSH client configuration to Pantheon hosts
rm -rf ~/.ssh/known_hosts

mkdir -p ~/.ssh && chmod 700 ~/.ssh && touch ~/.ssh/known_hosts && touch ~/.ssh/config

cp .ci/deploy/pantheon/ssh-config ~/.ssh/config

# Set up git aliases for conflict resolution

cat << EOF >> ~/.gitconfig
[alias]
  # Resolve ncoming merge conflict in favor of the current working branch
    ours = "!f() { git checkout --ours $@ && git add $@; }; f"
  # Resolve all incoming merge conflicts in favor of the current working branch
    all-ours = "!f() { [ -z \"$@\" ] && set - '.'; git checkout --ours -- \"$@\"; git add -u -- \"$@\"; }; f"
  # Resolve incoming merge conflict in favor of the upstream repo
    theirs = "!f() { git checkout --theirs $@ && git add $@; }; f"
  # Resolve all incoming merge conflicts in favor of the upstream repo
    all-theirs = "!f() { [ -z \"$@\" ] && set - '.'; git checkout --theirs -- \"$@\"; git add -u -- \"$@\"; }; f"
  # List merge conflicts
    conflicts = !git ls-files -u | cut -f 2 | sort -u
EOF

SITE_ID=$(terminus site:lookup "$TERMINUS_SITE")

ssh-keyscan -t rsa -p 2222 "appserver.dev.${SITE_ID}.drush.in" >>~/.ssh/known_hosts
ssh-keyscan -t rsa -p 2222 "codeserver.dev.${SITE_ID}.drush.in" >>~/.ssh/known_hosts

# Override default workflow:wait timeout of 180 seconds via terminus robo config
cp .ci/deploy/pantheon/config.yml ~/.terminus

# Set variables for Pantheon deploy branches and environments

setup_deploy_targets () {

  echo "Configuring deploy targets based on the current CI context."

  if [[ $CI_BRANCH = "$DEVELOPMENT_BRANCH" ]]
  then
      echo "CI branch is $CI_BRANCH which is the development branch. TERMINUS_ENV set to develop."
      TERMINUS_ENV="develop"
      DEPLOY_BRANCH="$DEVELOPMENT_BRANCH"
      echo "DEPLOY_BRANCH set to $DEPLOY_BRANCH."
  elif [[ $CI_BRANCH = "$PRODUCTION_BRANCH" ]]
  then
      echo "CI branch is $CI_BRANCH which is the production branch. TERMINUS_ENV set to dev."
      TERMINUS_ENV="dev"
      DEPLOY_BRANCH="$PRODUCTION_BRANCH"
      echo "DEPLOY_BRANCH set to $DEPLOY_BRANCH."
  elif [[ -n "$PR_NUMBER" ]]
  then
      echo "This is a Pull Request and the PR # is $PR_NUMBER. TERMINUS_ENV set to $TERMINUS_ENV."
      #TERMINUS_ENV=pr-$PR_NUMBER
      DEPLOY_BRANCH=$TERMINUS_ENV
      echo "DEPLOY_BRANCH set to $DEPLOY_BRANCH."
  fi
  
}

setup_deploy_targets

# Wait for running workflows to complete - this is a workaround for workflow:wait unreliable behavior

check_workflows () {
  local id=$1
  
  START_TIME="$(date -u +%s)"
  TIMEOUT_SEC=1400
  local retries=90
  while (($(date +%s) < $START_TIME + $TIMEOUT_SEC)); do
    set +e
    CURRENT_TIME="$(date -u +%s)"
    elapsed_seconds=$(($CURRENT_TIME-$START_TIME))
    if [[ -z "$id" ]]; then
      WORKFLOW_RUNNING=$(terminus workflow:list "$TERMINUS_SITE" --fields=id,status,env,workflow | grep $TERMINUS_ENV)
    else
      WORKFLOW_STATUS=$(terminus workflow:info:status  $TERMINUS_SITE --id=$id --field=status)
    fi
    set +e
    if [ $elapsed_seconds -gt $TIMEOUT_SEC ]; then
      echo "timeout of $TIMEOUT_SEC sec"
      exit 124
    fi

    echo "Total workflow time elapsed is $elapsed_seconds sec"

    if [[ "$WORKFLOW_RUNNING" =~ "running" ]]
    then
      echo "Workflows running, waiting 15 seconds and checking again."
      sleep 15
    elif [[ "$WORKFLOW_RUNNING" =~ "failed" || "$WORKFLOW_STATUS" =~ "failed" ]]
    then
      echo "Workflows failed, exiting."
      return 1
    else
      echo "Workflows completed successfully."
      break
    fi
  done
}

check_bootstrap () {
  local retries=3
  local DRUPAL_BOOTSTRAPPED=1
  while [ $retries -gt 0 ]; do
      terminus -n drush "$TERMINUS_SITE"."$TERMINUS_ENV" -- status --field=bootstrap 2>&1
      DRUPAL_BOOTSTRAPPED="$?"
    if [[ "$DRUPAL_BOOTSTRAPPED" == 0 ]]; then
      echo "Drupal bootstrapped successfully."
      break
    else
      echo "Drupal not bootstrapped, waiting 15 seconds and checking again."
      sleep 15
      retries=$((retries - 1))
      if [[ $retries -eq 0 ]]; then
        echo "Drupal not bootstrapped after 3 retries, exiting."
        DRUPAL_BOOTSTRAPPED=1
      fi
    fi
  done

  return $DRUPAL_BOOTSTRAPPED

}


check_md () {
    set +e
    local MD_STATUS=1
    # Create multidev in background if it doesn't exist - this is to speed up builds
    echo "Creating multidev as a background task if it doesn't exist."
    # Push to multidev env, create branch on Pantheon if it doesnt exist
    TERMINUS_ENV_EXISTS=$(terminus env:list "$TERMINUS_SITE" --field=ID | grep -w $TERMINUS_ENV)
    # Doesn't exist; create it
    # If terminus_env_exists is not empty, the environment exists. Check that MD_STATUS is not 0, if it is, then the multidev exists and we can skip creation.
    if [[ -n "$TERMINUS_ENV_EXISTS" ]]
    then
        echo "Site $TERMINUS_SITE has a $TERMINUS_ENV multidev. Skipping creation."
        MD_STATUS=0
        
        # echo "Checking if Drupal can be bootstrapped on existing environment."
        # if check_bootstrap; then
        #   MD_STATUS=0
        # else
        #   echo "Drupal not bootstrapped on existing environment, exiting."
        #   MD_STATUS=1
        # fi

    else [[ -z "$TERMINUS_ENV_EXISTS" ]]
        echo "Site $TERMINUS_SITE does not have a $TERMINUS_ENV multidev. Creating."
        ./.ci/deploy/pantheon/bg.sh "terminus -n multidev:create $TERMINUS_SITE.$MDS_FROM $TERMINUS_ENV --yes"
        id=$(terminus workflow:list "$TERMINUS_SITE" --fields=id,status,env,workflow | grep -w 'Create a Multidev environment' |  grep -v "succeeded" | awk '{print $1}' | head -n 1)
        check_workflows $id
        MD_STATUS=$?
        if [[ $MD_STATUS -ne 0 ]]; then
          echo "Multidev workflow steps failed, deleting in background and exiting."
          ./.ci/deploy/pantheon/bg.sh "terminus -n multidev:delete $TERMINUS_SITE.$TERMINUS_ENV --yes"
          MD_STATUS=1
        fi
    fi

    return $MD_STATUS
}

check_composer () {

  echo "Running composer checks on Pantheon environment $TERMINUS_ENV."

  composer validate --no-check-all --ansi && true
  CHECK_BUILD="$?"

  if [[ "$CHECK_BUILD" == 2 ]]; then
    echo "Composer checks failed, attempt to auto-resolve errors on Pantheon remote."

    git checkout -b remote-buildfix

    rm composer.lock

    # Rebuild lockfile and test IC build
    composer clean-install

    git add .

    git commit -m "Resolve dependency conflicts on Pantheon remote site repo."


    echo "Pushing code to $TERMINUS_ENV environment if env checks are successful."
    git push pantheon remote-buildfix:refs/heads/$DEPLOY_BRANCH --force

    git checkout -f origin/$CI_BRANCH

    git status

  else
    echo "Composer checks passed."
  fi
}


remove_untracked () {
    set +e
    UNTRACKED_FILES=$(git status -u)

    if [[ -n "$UNTRACKED_FILES" ]]; then
      echo "Untracked files found, attempting to remove."
      git clean -f -d
    else
      echo "No untracked files found."
    fi
}

function resolve_conflicts {
    # SET CONFLICTS_LIST to the output of git conflicts
  CONFLICTS_LIST=$(git conflicts)

    if [[ -n "$CONFLICTS_LIST" ]]; then
      echo "Merge conflicts found, attempting to resolve."
      git theirs composer.lock
      git all-theirs
      git status
      remove_untracked
      git commit --no-edit
      # git commit -m "Resolving merge conflicts for $GITHUB_SHA on Github branch $CI_BRANCH."
    fi
}

PANTHEON_REMOTE=$(terminus connection:info "$TERMINUS_SITE.dev" --field=git_url)

echo "Adding Pantheon remote to git repo. Value of PANTHEON_REMOTE: $PANTHEON_REMOTE"

# Setup pantheon repo remote
git remote add pantheon $PANTHEON_REMOTE

# Sync Pantheon upstream branch
git fetch pantheon


commit_frontend () {
  #if [[ $(git status --porcelain | grep build) ]]; then
    echo "Uncommitted frontend changes detected in local repo, committing to avoid merge failures."
    git add web/profiles/custom/*
    git commit -m "Frontend Build for commit $GITHUB_SHA on Github branch $CI_BRANCH."
  #else
   # echo No local changes to commit
  # fi
}

build_artifact () {
  if [[ $(git status --porcelain) ]]; then
  echo "Committing build artifact assets."
  git add -A -f
  git commit -m "Build and deploy artifact for Github branch $CI_BRANCH."
else
  echo No build artifact assets to commit
fi
}

git_cleanup () {

  MODIFIED_OR_ADDED_LIST=$(git status --porcelain)

  if [[ -n "$MODIFIED_OR_ADDED_LIST" ]]; then
    echo "Uncommitted files found:"
    echo $MODIFIED_OR_ADDED_LIST

    echo  "Attempting to remove uncommitted files."
    git clean -fdX
    git status
    git stash
  fi
}

#git_cleanup

UPSTREAM_TARGET_BRANCH=${TARGET_BRANCH:-"develop"}

# if this is a PR, then we need to pull the base branch of the PR in case there are site-specific overrides which have been deploye to the target environment
if [[ $CI_BRANCH != "$DEVELOPMENT_BRANCH" && $CI_BRANCH != "$PRODUCTION_BRANCH" ]]; then
  echo "Syncing $UPSTREAM_TARGET_BRANCH branch from pantheon remote to $TERMINUS_ENV environment."

  if [[ -f "composer.lock" ]]; then
    echo "composer.lock found in current working directory, removing to avoid merge conflicts."
    rm composer.lock
  fi

  set +e

  commit_frontend && build_artifact && remove_untracked && git pull pantheon $UPSTREAM_TARGET_BRANCH -X theirs --ff --no-edit

  resolve_conflicts

  check_composer

  echo "Checking if $DEPLOY_BRANCH branch exists on Pantheon, syncing."
  check_branch=$(git ls-remote --heads pantheon $DEPLOY_BRANCH)
 [[ -n ${check_branch} ]] && build_artifact && remove_untracked && git pull pantheon $DEPLOY_BRANCH -X theirs --ff --no-edit
  echo "Pushing code to $TERMINUS_ENV environment and DEPLOY_BRANCH $DEPLOY_BRANCH if env checks are successful."
  check_md && git push pantheon HEAD:refs/heads/"$DEPLOY_BRANCH" --force
  # git remote remove pantheon
elif [[ $CI_BRANCH = "$DEVELOPMENT_BRANCH" ]]; then
  echo "CI branch is $CI_BRANCH. TERMINUS_ENV is $TERMINUS_ENV and DEPLOY_BRANCH is $DEPLOY_BRANCH."
 # TERMINUS_ENV="develop"
  set +e
  commit_frontend && build_artifact && remove_untracked && git pull pantheon $DEPLOY_BRANCH -X theirs --ff --no-edit

  resolve_conflicts

  check_composer

  echo "Checking if $DEPLOY_BRANCH branch exists on Pantheon, syncing."
  check_branch=$(git ls-remote --heads pantheon $DEPLOY_BRANCH)
 [[ -n ${check_branch} ]] && build_artifact && remove_untracked && git pull pantheon $DEPLOY_BRANCH -X theirs --ff --no-edit
  echo "Pushing code to $TERMINUS_ENV environment and DEPLOY_BRANCH $DEPLOY_BRANCH if env checks are successful."
  check_md && git push pantheon HEAD:refs/heads/"$DEPLOY_BRANCH" --force

else [[ $CI_BRANCH = "$PRODUCTION_BRANCH" ]];
  #TERMINUS_ENV="dev"
 # echo "CI branch is $CI_BRANCH which is the production branch. TERMINUS_ENV set to dev."
 echo "CI branch is $CI_BRANCH. TERMINUS_ENV is $TERMINUS_ENV and DEPLOY_BRANCH is $DEPLOY_BRANCH."

  commit_frontend && build_artifact && remove_untracked && git pull pantheon $DEPLOY_BRANCH -X theirs --ff --no-edit

  resolve_conflicts

  check_composer

  echo "Checking if $DEPLOY_BRANCH branch exists on Pantheon, syncing."
  check_branch=$(git ls-remote --heads pantheon $DEPLOY_BRANCH)
 [[ -n ${check_branch} ]] && build_artifact && remove_untracked && git pull pantheon $DEPLOY_BRANCH -X theirs --ff --no-edit
  echo "Pushing code to $TERMINUS_ENV environment and DEPLOY_BRANCH $DEPLOY_BRANCH if env checks are successful."
  git push pantheon HEAD:refs/heads/"$DEPLOY_BRANCH" --force

fi


# # When deploying the develop branch, compare commit sha to the HEAD of the
# # production branch. Skip deploy when they match - the main branch deploy is
# # the one we want when a release is pushing updates to bh.
# if [[ $CI_BRANCH = "$DEVELOPMENT_BRANCH" ]]
# then
#     TERMINUS_ENV="develop"
#       check_md

#       echo "Pushing code to $TERMINUS_ENV environment if env checks are successful."
#       commit_frontend
#       build_artifact
#       git remote add pantheon $PANTHEON_REMOTE
#       git -C /__w/ul-drupal-pantheon/ul-drupal-pantheon checkout -f -B $TERMINUS_ENV
#       git push pantheon HEAD:refs/heads/"$DEPLOY_BRANCH" --force
#     # Get production branch head sha
#     git fetch origin "$PRODUCTION_BRANCH"
#     PROD_HEAD_SHA=$(git rev-parse origin/"$PRODUCTION_BRANCH")
#     if [[ $GITHUB_SHA = "$PROD_HEAD_SHA" ]]
#     then
#         echo "Skipping deploy of $GITHUB_SHA on $DEVELOPMENT_BRANCH. Commit will be deployed via $PRODUCTION_BRANCH."
#         exit 0
#     fi
# fi

# echo "Value of TERMINUS_ENV is $TERMINUS_ENV."

# # Force dev environment for the master branch, develop for dev either primary branch
# if [[ $CI_BRANCH = "$PRODUCTION_BRANCH" ]]
# then
#     echo "Found branch $CI_BRANCH. TERMINUS_ENV set to dev."
#     TERMINUS_ENV="dev"
#     git push pantheon HEAD:refs/heads/"$DEPLOY_BRANCH" --force
#    # git remote remove pantheon
# # Non-master branch; check if a mulitdev exists
# else
#     set +e
#     # Push to multidev env, create branch on Pantheon if it doesnt exist
#     git remote add pantheon $PANTHEON_REMOTE
#     git push pantheon HEAD:refs/heads/"$DEPLOY_BRANCH" --force
#     check_md
# fi


check_push () {
  local retries=3
  while [ $retries -gt 0 ]; do
      # Push to Pantheon
    echo "Pushing DEPLOY_BRANCH $DEPLOY_BRANCH to TERMINUS_ENV $TERMINUS_ENV."
    set +e
    git remote add pantheon $PANTHEON_REMOTE
    check_branch=$(git ls-remote --heads pantheon $DEPLOY_BRANCH)
    [[ -n ${check_branch} ]] && build_artifact && git pull pantheon $DEPLOY_BRANCH-X theirs --ff --no-edit
    # git remote remove pantheon
      #wait $PID
    echo "Pushing code to $TERMINUS_ENV environment if env checks are successful."
    # check_md && terminus -n build:env:push "$TERMINUS_SITE.$TERMINUS_ENV" --yes --no-git-force
    check_md
    build_artifact
    git remote add pantheon $PANTHEON_REMOTE
    git -C /__w/ul-drupal-pantheon/ul-drupal-pantheon checkout -f -B $TERMINUS_ENV
    git push pantheon HEAD:refs/heads/"$DEPLOY_BRANCH" --force

    PUSH_STATUS="$?"
    if [[ "$PUSH_STATUS" == 0 ]]
    then
      echo "Pushed to Pantheon $TERMINUS_SITE.$TERMINUS_ENV successfully."
      break
    else
      echo "Code push not successful, waiting 15 seconds and trying sync again."
      sleep 15
      retries=$((retries - 1))
    fi
  done
}

#check_push


check_workflows

echo "Pantheon workflows completed, waking idle environements."


# funciton to wake environment after waiting for $PID to finish

wake_env () {
  # wait $PID
  echo "Waking $TERMINUS_ENV environment."
  check_md && terminus -n env:wake "$TERMINUS_SITE.$TERMINUS_ENV"
}

wake_env


echo "Checking if drupal can be bootstrapped"

# Note that drush 11 returns exist code of command, not drush command result itself

check_bootstrap

drush_cr () {
  # Clear Drupal cache
echo "Clearing cache..."
  retries=2
  while [[ $retries -gt 0 ]]; do
    terminus -n drush "$TERMINUS_SITE"."$TERMINUS_ENV" -- cr 2>&1
    if [ "$?" = "0" ]; then
      break
    else
      echo "::warning:: drush cache:rebuild command failed - retrying in 30 seconds."
      sleep 30
      retries=$((retries-1))
    fi
  done
}

drush_cr

# Run drush updb, retrying up to 3 times if it fails
drush_updb () {
echo "Running Drupal updates..."
  retries=2
  while [[ $retries -gt 0 ]]; do
    terminus -n drush "$TERMINUS_SITE"."$TERMINUS_ENV" -- updb -y 2>&1
    if [ "$?" = "0" ]; then
      break
    else
      echo "::warning:: drush updb command failed - retrying in 30 seconds."
      sleep 30
      retries=$((retries-1))
    fi
  done
}

drush_entup () {
  echo "Checking for deprecated entity update hooks"
    local ENTUP_STATUS=1
    PENDING_ENTUP=$(terminus -n drush "$TERMINUS_SITE"."$TERMINUS_ENV" -- updbst --field=type | grep -w entity-update)
    if [[ -z $PENDING_ENTUP ]]; then
      echo "No pending entity schema updates."
      ENTUP_STATUS=0
    else
      echo "Pending entity schema updates found, attempting to run."
      retries=3
      while [[ $retries -gt 0 ]]; do
        terminus -n drush "$TERMINUS_SITE"."$TERMINUS_ENV" -- en devel_entity_updates -y 2>&1
        terminus -n drush "$TERMINUS_SITE"."$TERMINUS_ENV" -- entup -y 2>&1
      if [ "$?" = "0" ]; then
        ENTUP_STATUS=0
        break
      else
        echo "::warning:: drush entup command failed - retrying in 10 seconds."
        sleep 10
        retries=$((retries-1))
      fi
      done
    fi
    return $ENTUP_STATUS
}

# TODO: Asses if this is always needed for UL sites
#drush_entup

drush_updb


# Run config:import 
drush_cim () {
echo "Importing drupal configuration..."
  retries=3
  while [[ $retries -gt 0 ]]; do
    terminus -n drush "$TERMINUS_SITE"."$TERMINUS_ENV" -- config:import -y 2>&1
    if [ "$?" = "0" ]; then
      break
    else
      echo "::warning:: drush config:import command failed - retrying in 30 seconds."
      sleep 30
      retries=$((retries-1))
    fi
  done
}

drush_cim

platform_clear_cache () {
  echo "Clearing all Pantheon caches..."
  retries=2
  while [[ $retries -gt 0 ]]; do
   # Clear the environment cache
  terminus -n env:clear-cache "$TERMINUS_SITE"."$TERMINUS_ENV"
    if [ "$?" = "0" ]; then
      break
    else
      echo "::warning:: clearing Pantheon caches failed - retrying in 30 seconds."
      sleep 30
      retries=$((retries-1))
    fi
  done
}

platform_clear_cache

# Comment with the URL for pull requests
if [[ -n "$PR_NUMBER" ]]
then
    echo "Commenting on pull request."
    terminus build:comment:add:pr --pr_id="$PR_NUMBER" --message="Deployment to https://${TERMINUS_ENV}-${TERMINUS_SITE}.pantheonsite.io successful."
fi

# Delete multidevs associated with closed or merged pull merge requests.
terminus -n build:env:delete:pr "$TERMINUS_SITE" --yes
