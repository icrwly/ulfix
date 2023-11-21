<?php

namespace Drupal\ul_report;

use Drupal\Core\Database\Connection;
use Drupal\Core\Config\ConfigFactory;
use Drupal\views\Views;
use Drupal\Component\Datetime\DateTimePlus;
use Drupal\node\NodeInterface;
use PHPUnit\Framework\Exception;

/**
 * Class UlMarketoService.
 */
class UlReportService {

  /**
   * ConfigFactory service.
   *
   * @var \Drupal\Core\Config\Config|\Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * Database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Entity Field Manager service.
   *
   * @var \Drupal\Core\Entity\EntityFieldManager
   */
  protected $entityFieldManager;

  /**
   * Supported languages.
   *
   * @var array
   */
  protected $languages;

  /**
   * Constructs a new UlMarketoService object.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config
   *   Config factory object.
   * @param \Drupal\Core\Database\Connection $connection
   *   Database object.
   */
  public function __construct(ConfigFactory $config, Connection $connection) {
    $this->config = $config;
    $this->connection = $connection;
    $this->languages = array_keys(\Drupal::languageManager()->getLanguages());
  }

  /**
   * Update the marketo_report table with the results of a custom query.
   */
  public function updateMarketoReport() {

    // Truncate marketo_report table.
    $this->connection->query('truncate marketo_report');

    // Create/replace the marketo_report view.
    $marketo_report_sql = "
    insert into marketo_report
    select
    distinct
    n.nid,
    n.created,
    n.changed,
    n.status,
    n.uid,
    u.name as user_name,
    n.title,
    n.type,
    n.langcode,
    marketo_routing.field_shared_marketo_forms_instance as instance,
    marketo_routing.field_shared_marketo_forms_sub_cou as sub_cou,
    marketo_routing.field_shared_marketo_forms_last_interest as last_interest,
    header_cta.field_shared_marketo_link_value as header_cta,

    CASE
      WHEN header_cta.field_shared_marketo_link_value = 'generic_form' THEN 'Contact Form (old)'
      WHEN header_cta.field_shared_marketo_link_value =' contact_form_configurable' THEN 'Contact Form (new)'
      WHEN header_cta.field_shared_marketo_link_value = 'email_form' THEN 'Newsletter Form'
      WHEN header_cta.field_shared_marketo_link_value = 'event_form' THEN 'Event Registration'
      WHEN header_cta.field_shared_marketo_link_value = 'gated_content_form' THEN 'Gated Content Form'
      WHEN header_cta.field_shared_marketo_link_value = 'mkto_pref_ctr' THEN 'Preference Center'
      ELSE ''
    END as header_cta_form_name,
    rr_cta.field_shared_rr_marketo_cta_value as rr_cta,

    CASE
      WHEN rr_cta.field_shared_rr_marketo_cta_value = 'generic_form' THEN 'Contact Form (old)'
      WHEN rr_cta.field_shared_rr_marketo_cta_value = 'contact_form_configurable' THEN 'Contact Form (new)'
      WHEN rr_cta.field_shared_rr_marketo_cta_value = 'email_form' THEN 'Newsletter Form'
      WHEN rr_cta.field_shared_rr_marketo_cta_value = 'event_form' THEN 'Event Registration'
      WHEN rr_cta.field_shared_rr_marketo_cta_value = 'gated_content_form' THEN 'Gated Content Form'
      WHEN rr_cta.field_shared_rr_marketo_cta_value = 'mkto_pref_ctr' THEN 'Preference Center'
      ELSE ''
    END as rr_cta_form_name,

    if(paragraphs.marketo_paragraphs is null,'',paragraphs.marketo_paragraphs) as marketo_paragraphs,
    if(customizations.form_types is null,'',customizations.form_types) as form_types,
    if(customizations.marketo_customizations is null,'',customizations.marketo_customizations) as marketo_customizations

    from
    node_field_data n
    left join node__field_shared_marketo_forms marketo_routing on n.nid = marketo_routing.entity_id and n.type = marketo_routing.bundle and n.langcode = marketo_routing.langcode
    left join node__field_shared_marketo_link header_cta on n.nid = header_cta.entity_id and n.type = header_cta.bundle and n.langcode = header_cta.langcode
    left join node__field_shared_rr_marketo_cta rr_cta on n.nid = rr_cta.entity_id and n.type = rr_cta.bundle and n.langcode = rr_cta.langcode
    left join node__field_shared_marketo_custom on n.nid = node__field_shared_marketo_custom.entity_id and n.type = node__field_shared_marketo_custom.bundle and n.langcode = node__field_shared_marketo_custom.langcode
    left join marketo_form_field_data m on node__field_shared_marketo_custom.field_shared_marketo_custom_target_id = m.id  and m.parent_type = 'node'
    left join users_field_data u on n.uid = u.uid
    left join
    (
    -- BEGIN MARKETO PARAGRAPH SUBQUERY
  select
  p.nid,
  group_concat(p.form_name) as marketo_paragraphs
  from
  (
  -- BEGIN PARAGRAPHS
  -- field_campaign_page_marketo_form
  select
  n.nid,
  CASE
    WHEN m.field_marketo_form_value = 'generic_form' THEN 'Contact Form (old)'
    WHEN m.field_marketo_form_value = 'contact_form_configurable' THEN 'Contact Form (new)'
    WHEN m.field_marketo_form_value = 'email_form' THEN 'Newsletter Form'
    WHEN m.field_marketo_form_value = 'event_form' THEN 'Event Registration'
    WHEN m.field_marketo_form_value = 'gated_content_form' THEN 'Gated Content Form'
    WHEN m.field_marketo_form_value = 'mkto_pref_ctr' THEN 'Preference Center'
    ELSE ''
  END as form_name
  from
  node_field_data n
  join node__field_campaign_page_marketo_form f on n.nid = f.entity_id and n.langcode = f.langcode
  join paragraphs_item_field_data p on f.field_campaign_page_marketo_form_target_id = p.id and p.type in ('campaign_form','marketo_form','text_and_form')
  join paragraph__field_marketo_form m on p.id = m.entity_id and m.bundle in ('campaign_form','marketo_form','text_and_form')

  UNION

  -- field_of_content
  select
  n.nid,
  CASE
    WHEN m.field_marketo_form_value = 'generic_form' THEN 'Contact Form (old)'
    WHEN m.field_marketo_form_value = 'contact_form_configurable' THEN 'Contact Form (new)'
    WHEN m.field_marketo_form_value = 'email_form' THEN 'Newsletter Form'
    WHEN m.field_marketo_form_value = 'event_form' THEN 'Event Registration'
    WHEN m.field_marketo_form_value = 'gated_content_form' THEN 'Gated Content Form'
    WHEN m.field_marketo_form_value = 'mkto_pref_ctr' THEN 'Preference Center'
    ELSE ''
  END as form_name
  from
  node_field_data n
  join node__field_of_content f on n.nid = f.entity_id and n.langcode = f.langcode
  join paragraphs_item_field_data p on f.field_of_content_target_id = p.id and p.type in ('campaign_form','marketo_form','text_and_form')
  join paragraph__field_marketo_form m on p.id = m.entity_id and m.bundle in ('campaign_form','marketo_form','text_and_form')

  UNION

  -- field_landing_page_content
  select
  n.nid,
  CASE
    WHEN m.field_marketo_form_value = 'generic_form' THEN 'Contact Form (old)'
    WHEN m.field_marketo_form_value = 'contact_form_configurable' THEN 'Contact Form (new)'
    WHEN m.field_marketo_form_value = 'email_form' THEN 'Newsletter Form'
    WHEN m.field_marketo_form_value = 'event_form' THEN 'Event Registration'
    WHEN m.field_marketo_form_value = 'gated_content_form' THEN 'Gated Content Form'
    WHEN m.field_marketo_form_value = 'mkto_pref_ctr' THEN 'Preference Center'
    ELSE ''
  END as form_name
  from
  node_field_data n
  join node__field_landing_page_content f on n.nid = f.entity_id and n.langcode = f.langcode
  join paragraphs_item_field_data p on f.field_landing_page_content_target_id = p.id and p.type in ('campaign_form','marketo_form','text_and_form')
  join paragraph__field_marketo_form m on p.id = m.entity_id and m.bundle in ('campaign_form','marketo_form','text_and_form')

  UNION

  -- field_news_content
  select
  n.nid,
  CASE
    WHEN m.field_marketo_form_value = 'generic_form' THEN 'Contact Form (old)'
    WHEN m.field_marketo_form_value = 'contact_form_configurable' THEN 'Contact Form (new)'
    WHEN m.field_marketo_form_value = 'email_form' THEN 'Newsletter Form'
    WHEN m.field_marketo_form_value = 'event_form' THEN 'Event Registration'
    WHEN m.field_marketo_form_value = 'gated_content_form' THEN 'Gated Content Form'
    WHEN m.field_marketo_form_value = 'mkto_pref_ctr' THEN 'Preference Center'
    ELSE ''
  END as form_name
  from
  node_field_data n
  join node__field_news_content f on n.nid = f.entity_id and n.langcode = f.langcode
  join paragraphs_item_field_data p on f.field_news_content_target_id = p.id and p.type in ('campaign_form','marketo_form','text_and_form')
  join paragraph__field_marketo_form m on p.id = m.entity_id and m.bundle in ('campaign_form','marketo_form','text_and_form')

  UNION

  -- field_resource_content
  select
  n.nid,
  CASE
    WHEN m.field_marketo_form_value = 'generic_form' THEN 'Contact Form (old)'
    WHEN m.field_marketo_form_value = 'contact_form_configurable' THEN 'Contact Form (new)'
    WHEN m.field_marketo_form_value = 'email_form' THEN 'Newsletter Form'
    WHEN m.field_marketo_form_value = 'event_form' THEN 'Event Registration'
    WHEN m.field_marketo_form_value = 'gated_content_form' THEN 'Gated Content Form'
    WHEN m.field_marketo_form_value = 'mkto_pref_ctr' THEN 'Preference Center'
    ELSE ''
  END as form_name
  from
  node_field_data n
  join node__field_resource_content f on n.nid = f.entity_id and n.langcode = f.langcode
  join paragraphs_item_field_data p on f.field_resource_content_target_id = p.id and p.type in ('campaign_form','marketo_form','text_and_form')
  join paragraph__field_marketo_form m on p.id = m.entity_id and m.bundle in ('campaign_form','marketo_form','text_and_form')

  UNION

  -- field_page_content
  select
  n.nid,
  CASE
    WHEN m.field_marketo_form_value = 'generic_form' THEN 'Contact Form (old)'
    WHEN m.field_marketo_form_value = 'contact_form_configurable' THEN 'Contact Form (new)'
    WHEN m.field_marketo_form_value = 'email_form' THEN 'Newsletter Form'
    WHEN m.field_marketo_form_value = 'event_form' THEN 'Event Registration'
    WHEN m.field_marketo_form_value = 'gated_content_form' THEN 'Gated Content Form'
    WHEN m.field_marketo_form_value = 'mkto_pref_ctr' THEN 'Preference Center'
    ELSE ''
  END as form_name
  from
  node_field_data n
  join node__field_page_content f on n.nid = f.entity_id and n.langcode = f.langcode
  join paragraphs_item_field_data p on f.field_page_content_target_id = p.id and p.type in ('campaign_form','marketo_form','text_and_form')
  join paragraph__field_marketo_form m on p.id = m.entity_id and m.bundle in ('campaign_form','marketo_form','text_and_form')

  UNION

  -- field_landing_page_content
  select
  n.nid,
  CASE
    WHEN m.field_marketo_form_value = 'generic_form' THEN 'Contact Form (old)'
    WHEN m.field_marketo_form_value = 'contact_form_configurable' THEN 'Contact Form (new)'
    WHEN m.field_marketo_form_value = 'email_form' THEN 'Newsletter Form'
    WHEN m.field_marketo_form_value = 'event_form' THEN 'Event Registration'
    WHEN m.field_marketo_form_value = 'gated_content_form' THEN 'Gated Content Form'
    WHEN m.field_marketo_form_value = 'mkto_pref_ctr' THEN 'Preference Center'
    ELSE ''
  END as form_name
  from
  node_field_data n
  join node__field_landing_page_content f on n.nid = f.entity_id and n.langcode = f.langcode
  join paragraphs_item_field_data p on f.field_landing_page_content_target_id = p.id and p.type in ('campaign_form','marketo_form','text_and_form')
  join paragraph__field_marketo_form m on p.id = m.entity_id and m.bundle in ('campaign_form','marketo_form','text_and_form')

  UNION

  -- field_help_content
  select
  n.nid,
  CASE
    WHEN m.field_marketo_form_value = 'generic_form' THEN 'Contact Form (old)'
    WHEN m.field_marketo_form_value = 'contact_form_configurable' THEN 'Contact Form (new)'
    WHEN m.field_marketo_form_value = 'email_form' THEN 'Newsletter Form'
    WHEN m.field_marketo_form_value = 'event_form' THEN 'Event Registration'
    WHEN m.field_marketo_form_value = 'gated_content_form' THEN 'Gated Content Form'
    WHEN m.field_marketo_form_value = 'mkto_pref_ctr' THEN 'Preference Center'
    ELSE ''
  END as form_name
  from
  node_field_data n
  join node__field_help_content f on n.nid = f.entity_id and n.langcode = f.langcode
  join paragraphs_item_field_data p on f.field_help_content_target_id = p.id and p.type in ('campaign_form','marketo_form','text_and_form')
  join paragraph__field_marketo_form m on p.id = m.entity_id and m.bundle in ('campaign_form','marketo_form','text_and_form')

  UNION

  -- field_event_content
  select
  n.nid,
  CASE
    WHEN m.field_marketo_form_value = 'generic_form' THEN 'Contact Form (old)'
    WHEN m.field_marketo_form_value = 'contact_form_configurable' THEN 'Contact Form (new)'
    WHEN m.field_marketo_form_value = 'email_form' THEN 'Newsletter Form'
    WHEN m.field_marketo_form_value = 'event_form' THEN 'Event Registration'
    WHEN m.field_marketo_form_value = 'gated_content_form' THEN 'Gated Content Form'
    WHEN m.field_marketo_form_value = 'mkto_pref_ctr' THEN 'Preference Center'
    ELSE ''
  END as form_name
  from
  node_field_data n
  join node__field_event_content f on n.nid = f.entity_id and n.langcode = f.langcode
  join paragraphs_item_field_data p on f.field_event_content_target_id = p.id and p.type in ('campaign_form','marketo_form','text_and_form')
  join paragraph__field_marketo_form m on p.id = m.entity_id and m.bundle in ('campaign_form','marketo_form','text_and_form')

  UNION

  -- field_tool_content
  select
  n.nid,
  CASE
    WHEN m.field_marketo_form_value = 'generic_form' THEN 'Contact Form (old)'
    WHEN m.field_marketo_form_value = 'contact_form_configurable' THEN 'Contact Form (new)'
    WHEN m.field_marketo_form_value = 'email_form' THEN 'Newsletter Form'
    WHEN m.field_marketo_form_value = 'event_form' THEN 'Event Registration'
    WHEN m.field_marketo_form_value = 'gated_content_form' THEN 'Gated Content Form'
    WHEN m.field_marketo_form_value = 'mkto_pref_ctr' THEN 'Preference Center'
    ELSE ''
  END as form_name
  from
  node_field_data n
  join node__field_tool_content f on n.nid = f.entity_id and n.langcode = f.langcode
  join paragraphs_item_field_data p on f.field_tool_content_target_id = p.id and p.type in ('campaign_form','marketo_form','text_and_form')
  join paragraph__field_marketo_form m on p.id = m.entity_id and m.bundle in ('campaign_form','marketo_form','text_and_form')

  order by
  nid,
  form_name
  -- END PARAGRAPHS
  ) p
  group by
  p.nid
  ) paragraphs on n.nid = paragraphs.nid

  -- END MARKETO PARAGRAPH SUBQUERY

    left join
    (
    select
    c.entity_id,
    group_concat(c.type) as form_types,
    group_concat(c.form_name) as marketo_customizations
    from
    (
    select
    distinct
    n.entity_id,
    n.bundle,
    m.type,
    m.parent_field_name,
    m.id,
    CASE
      WHEN m.type = 'generic_form' THEN 'Contact Form (old)'
      WHEN m.type = 'contact_form_configurable' THEN 'Contact Form (new)'
      WHEN m.type = 'email_form' THEN 'Newsletter Form'
      WHEN m.type = 'event_form' THEN 'Event Registration'
      WHEN m.type = 'gated_content_form' THEN 'Gated Content Form'
      WHEN m.type = 'mkto_pref_ctr' THEN 'Preference Center'
      ELSE ''
    END as form_name
    from
    node__field_shared_marketo_custom n
    left join marketo_form_field_data m on n.field_shared_marketo_custom_target_id = m.id  and m.parent_type = 'node'
    order by
    n.entity_id,
    form_name
    ) c
    group by
    c.entity_id
    ) customizations on n.nid = customizations.entity_id
    order by nid";

    $this->connection->query($marketo_report_sql);

    $view = Views::getView('marketo_report');
    $view->storage->invalidateCaches();

  }

  /**
   * Returns an array of media and file entities referenced in the node.
   *
   * @param array $data
   *   Array of referenced media and files.
   * @param object $entity
   *   Entity object.
   *
   * @return array
   *   Array of media and file entities.
   */
  public function getReferencedMediaAndFiles(array &$data, object &$entity) {

    $fields = \Drupal::service('entity_field.manager')->getFieldDefinitions($entity->getEntityType()->id(), $entity->bundle());
    $keys = array_keys($fields);

    foreach ($this->languages as $lang) {
      if ($entity->hasTranslation($lang)) {
        $entity_trans = $entity->getTranslation($lang);
        foreach ($keys as $f) {
          if (substr($f, 0, 6) == 'field_') {
            $field_type = $fields[$f]->getType();
            $settings = $fields[$f]->getSettings();
            switch ($field_type) {
              case 'file':
              case 'image':
                $value = $entity_trans->{$f}->getValue();
                foreach ($value as $v) {
                  $data[] = [
                    'langcode' => $lang,
                    'mid' => NULL,
                    'fid' => $v['target_id'],
                  ];
                }
                break;

              case 'entity_reference':
                switch ($settings['handler']) {
                  case 'default:media':
                    $value = $entity_trans->{$f}->getValue();
                    foreach ($value as $v) {
                      $mid = $v['target_id'];
                      $media = \Drupal::entityTypeManager()->getStorage('media')->load($mid);
                      if (!empty($media)) {
                        // We could interate through all of the media fields but
                        // we know there are two with file entity references.
                        switch ($media->bundle()) {
                          case 'file':
                            $value = $media->field_media_file->getValue();
                            foreach ($value as $v) {
                              $data[] = [
                                'langcode' => $lang,
                                'mid' => $mid,
                                'fid' => $v['target_id'],
                              ];
                            }
                            break;

                          case 'image':
                            $value = $media->field_media_image->getValue();
                            foreach ($value as $v) {
                              $data[] = [
                                'langcode' => $lang,
                                'mid' => $mid,
                                'fid' => $v['target_id'],
                              ];
                            }
                            break;
                        }
                      }
                    }
                    break;
                }
                break;

              case 'entity_reference_revisions':
                switch ($settings['target_type']) {
                  case 'paragraph':
                    $paragraphs = $entity_trans->{$f}->referencedEntities();
                    foreach ($paragraphs as $p) {
                      $this->getReferencedMediaAndFiles($data, $p);
                    }
                    break;
                }
                break;
            }
          }
        }
      }
    }

    $data = array_unique($data, SORT_REGULAR);
    return $data;
  }

  /**
   * Updates the node_media_file table for the given node id.
   *
   * @param object $node
   *   Node.
   */
  public function updateReferencedMediaAndFiles(&$node) {

    $data = [];
    $data = $this->getReferencedMediaAndFiles($data, $node);

    $nid = $node->id();

    // First delete all entries in node_media_file for the given $nid.
    $this->connection->delete('node_media_file')
      ->condition('nid', $nid)
      ->execute();

    // Insert new records for the supplied $nid and $data values.
    $query = $this->connection->insert('node_media_file')->fields([
      'nid',
      'langcode',
      'mid',
      'fid',
    ]);

    foreach ($data as $d) {
      $query->values([
        'nid' => $nid,
        'langcode' => $d['langcode'],
        'mid' => $d['mid'],
        'fid' => $d['fid'],
      ]);
    }

    $query->execute();
  }

  /**
   * Deleted the records in the node_media_file table for the given node id.
   *
   * @param string $nid
   *   Node ID.
   */
  public function deleteReferencedMediaAndFiles($nid) {
    // Fix the error: Object of class Drupal\node\Entity\Node could not be
    // converted to string in PDOStatement->execute().
    if ($nid instanceof NodeInterface) {
      $nid = $nid->id();
    }

    try {
      $deleted = $this->connection->delete('node_media_file')
        ->condition('nid', $nid)
        ->execute();
      return $deleted;
    }
    catch (Exception $e) {
      \Drupal::logger('UL Report')->error('Failed to delete data from node_media_file table: nid=' . $nid);
      return FALSE;
    }
  }

  /**
   * Update the node_media_file table for all nodes.
   *
   * @param array $ids
   *   Optional array of ids to update, else all nodes will be updated.
   */
  public function updateNodeMediaAndFiles(array $ids = NULL) {
    $start_time = new DateTimePlus();
    \Drupal::logger('UL Report')->notice('Node media and file update started at ' . $start_time->format('Y-m-d h:i:a'));

    $query = $this->connection->select('node_field_data', 'n');
    $query->fields('n', ['nid']);

    if (!empty($ids)) {
      $query->condition('n.nid', $ids, 'IN');
    }

    $result = $query->distinct()->execute()->fetchAll();

    foreach ($result as $row) {
      \Drupal::logger('UL Report')->notice('Updating referenced media and files for node ' . $row->nid);
      $node = \Drupal::entityTypeManager()->getStorage('node')->load($row->nid);
      $this->updateReferencedMediaAndFiles($node);
    }

    $end_time = new DateTimePlus();

    $elapsed_time = $start_time->diff($end_time);

    \Drupal::logger('UL Report')->notice('Node media and file completed in ' . $elapsed_time->format('%i minutes %s seconds'));

  }

  /**
   * Modify views query to sort language field by lang_name instead of langcode.
   *
   * @param object $query
   *   Query to modify.
   * @param int $index
   *   Index of sort array to modify.
   */
  public function orderByLanguageName(object $query, int $index) {

    $languages = \Drupal::languageManager()->getLanguages();

    $field_name = $query->orderby[$index]['field'];
    $when = [];
    foreach ($languages as $key => $l) {
      $when[] = "WHEN $field_name = '$key' THEN '" . $l->getName() . "'";
    }
    $sort_field = str_replace('.', '_', $field_name) . '_sort';
    $query->addField(NULL, 'CASE ' . implode(' ', $when) . ' END', $sort_field);
    $query->orderby[$index]['field'] = $sort_field;
  }

  /**
   * Modify views query: sort content type by label instead of machine name.
   *
   * @param object $query
   *   Query to modify.
   * @param int $index
   *   Index of sort array to modify.
   */
  public function orderByContentTypeName(object $query, int $index) {

    $entityTypeManager = \Drupal::service('entity_type.manager');

    $contentTypes = $entityTypeManager->getStorage('node_type')->loadMultiple();

    $field_name = $query->orderby[$index]['field'];
    $when = [];
    foreach ($contentTypes as $contentType) {
      $when[] = "WHEN $field_name = '" . $contentType->id() . "' THEN '" . $contentType->label() . "'";
    }

    $sort_field = str_replace('.', '_', $field_name) . '_sort';
    $query->addField(NULL, 'CASE ' . implode(' ', $when) . ' END', $sort_field);
    $query->orderby[$index]['field'] = $sort_field;
  }

}
