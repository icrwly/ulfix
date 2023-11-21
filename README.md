# Custom Upstream D9 Template for UL

This repository is a GitHub template for creating an enterprise Drupal 9 custom upstream project on the Pantheon platform. It extends Pantheon's standard [Drupal 9 recommended starter template](https://github.com/pantheon-upstreams/drupal-composer-managed) to provide:

- Matrixed site CI / CD via Github Actions
- Pantheon Build tools integration
- Pantheon Integrated Composer support
- Documentation templates
- Drupal settings and config scaffolding
- Pantheon-specific scaffolding and modules
- Quicksilver hooks library
- Various project and automation scripting
- Architecture decision record framework

This framework will continue to be built out by Professional Services and the README will be updated accordingly.

## Initial Installation

1. Clone the repository.
2. Run the pantheon-local.sh script. To run the script, type sh pantheon-local.sh "site name".  For example, sh pantheon-local.sh "emergo". The script will run composer install. generate the frontend build assets, import the database from Pantheon, and start ddev. 

## Migration next steps

Once the initial upstream template is generated, standard migration re-architecture steps can begin - these steps and requirements will be captured in Jira tasks.

## Architecture Decision Records

Significant project architecture decisions will be documented in the custom upstream in order to ensure standardizd best practices are being delivered across  PS projects and that customers have a historical decision log of the changes to their legacy architecture. 

An initial ADR is provided as an example, which was created with:

```
./vendor/bin/phpadr make:decision "Documenting architecture decisions" "Accepted"  --config="arch/adr.yml"
```


## General Guidance on Building Custom Upstreams

1. For the initial migration, use the exact version constraint used on the existing host and pin Drupal core the the exact version installed on the databases which will be migrated to Pantheon. Updates may be implemented after the initial migration if needed
