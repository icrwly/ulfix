<?php

namespace Drupal\ul_alerts\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Datetime\DateFormatter;
use Drupal\Core\Render\Renderer;
use Drupal\Core\Url;
use Drupal\ul_alerts\Entity\AlertInterface;
use Drupal\Core\Link;

/**
 * Class AlertController.
 *
 *  Returns responses for Alert routes.
 */
class AlertController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * Constructs a AlertController object.
   *
   * @param \Drupal\Core\Datetime\DateFormatter $date_formatter
   *   The date formatter service.
   * @param \Drupal\Core\Render\Renderer $renderer
   *   The renderer service.
   */
  public function __construct(DateFormatter $date_formatter, Renderer $renderer) {
    $this->dateFormatter = $date_formatter;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter'),
      $container->get('renderer')
    );
  }

  /**
   * Displays an Alert revision.
   *
   * @param int $ul_alert_revision
   *   The Alert revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($ul_alert_revision) {
    $ul_alert = $this->entityTypeManager()->getStorage('ul_alert')->loadRevision($ul_alert_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('ul_alert');

    return $view_builder->view($ul_alert);
  }

  /**
   * Page title callback for a Alert  revision.
   *
   * @param int $ul_alert_revision
   *   The Alert revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($ul_alert_revision) {
    $ul_alert = $this->entityTypeManager()->getStorage('ul_alert')->loadRevision($ul_alert_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $ul_alert->label(),
      '%date' => $this->dateFormatter->format($ul_alert->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Alert .
   *
   * @param \Drupal\ul_alerts\Entity\AlertInterface $ul_alert
   *   A Alert  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(AlertInterface $ul_alert) {
    $account = $this->currentUser();
    $langcode = $ul_alert->language()->getId();
    $langname = $ul_alert->language()->getName();
    $languages = $ul_alert->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $ul_alert_storage = $this->entityTypeManager()->getStorage('ul_alert');

    $build['#title'] = $has_translations
      ? $this->t('@langname revisions for %title', [
        '@langname' => $langname,
        '%title' => $ul_alert->label(),
      ])
      : $this->t('Revisions for %title', [
        '%title' => $ul_alert->label(),
      ]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all alert revisions") || $account->hasPermission('administer alert entities')));
    $delete_permission = (($account->hasPermission("delete all alert revisions") || $account->hasPermission('administer alert entities')));

    $rows = [];

    $vids = $ul_alert_storage->revisionIds($ul_alert);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\ul_alerts\AlertInterface $revision */
      $revision = $ul_alert_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $ul_alert->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.ul_alert.revision', [
            'ul_alert' => $ul_alert->id(),
            'ul_alert_revision' => $vid,
          ]));
        }
        else {
          $link = $ul_alert->toLink($date)->toString();
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            if ($has_translations) {
              $url = Url::fromRoute('entity.ul_alert.translation_revert', [
                'ul_alert' => $ul_alert->id(),
                'ul_alert_revision' => $vid,
                'langcode' => $langcode,
              ]);
            }
            else {
              $url = Url::fromRoute('entity.ul_alert.revision_revert', [
                'ul_alert' => $ul_alert->id(),
                'ul_alert_revision' => $vid,
              ]);
            }
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $url,
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.ul_alert.revision_delete', [
                'ul_alert' => $ul_alert->id(),
                'ul_alert_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['ul_alert_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
