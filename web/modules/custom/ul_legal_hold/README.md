## The Legal Hold Module

The Legal Hold Module creates a content entity that attaches to an existing piece of content that is to be placed on a Legal Hold.

This hold can have one or multiple revisions of the content attached to provide a "snapshot" of the content at a certain time, similar to the Internet Wayback Machine.

### Usage

**Enable the module**

1. Enable the Legal Hold module `drush en ul-legal-hold`
1. Clear caches `drush cr`
1. Give new permissions to appropriate roles.

**Attach a new hold**

1. While logged in as an administrator or someone with the new permissions, navigate to a piece of content.
1. Go to the edit form of the content.
1. Notice the `Legal Holds` tab and select it.
1. The first tab is all current holds attached to this content.  Select the `Add Legal Hold` button. _The entity has three fields for user entry:  Title, Description, and Held Revisions.  All are mandatory._
1. Fill out the text fields and select revisions.
1. Save the content.

This attaches the hold to the content.

**View holds attached to content**

1. Navigate to a piece of content with a hold attached.
2. Go to the edit form.
3. Click on the `Legal Holds` tab.
4. If there are holds attached to this content, they will appear in this tab.

**View all holds**

1. Navigate to the `Content` menu.
2. Click on the `Legal Holds` tab.
3. All holds will appear here.

**Search all holds**

1. While in the `Legal Holds` tab, enter text into either the Title or Description search box.
2. Click `Search`
3. Observe that correct results are showing.

**Delete hold**

Delete a hold the same way as any other content.

1. Navigate to the hold.
2. Edit the hold.
3. Delete the hold.

**@TODO** _Create better way to release content from hold_

## Notes

* A piece of content with a hold attached cannot be deleted until the hold is removed.
* Creating a hold on the current default revision will create a new revision of the held content with a log message indicating that the content has been placed on hold.
