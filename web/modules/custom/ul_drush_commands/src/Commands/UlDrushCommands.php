<?php

namespace Drupal\ul_drush_commands\Commands;

use Drush\Commands\DrushCommands;
use Drupal\user\Entity\User;
use Drupal\Core\Database\Connection;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\TypedData\TranslatableInterface;
use Drupal\taxonomy\Entity\Vocabulary;

/**
 * A Drush commandfile.
 */
class UlDrushCommands extends DrushCommands {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * Constructor.
   */
  public function __construct(Connection $connection) {
    $this->database = $connection;
  }

  /**
   * Drush command to reset the roles of users.
   *
   * @param string $environment
   *   Argument provided to the drush command for the sandbox environment.
   *
   * @command ul_drush_commands:setroles
   * @aliases ul-setroles
   * @usage ul_drush_commands:setroles sandbox
   *   Reset the roles for all active users who are content_editor.
   */
  public function setRoles($environment) {

    $host = \Drupal::request()->getHost();

    if ($environment == "sandbox" && (stristr($host, 'sbox') ||
      stristr($host, 'docksal'))) {
      // Get all valide users, array User objects.
      $users = $this->getValidUsers();
      $count = 0;
      foreach ($users as $user) {
        if ($this->addRolesToUser($user)) {
          $count++;
        }
      }

      $this->output()->writeln('Number of Users Updated on the site ' . $host . ': '
        . $count);
    }
    else {
      $this->output()->writeln('The command is for Sandbox site only, but not for '
        . $host . '!');
    }

  }

  /**
   * Help function to get valid users.
   *
   * @return array
   *   Array of User objects.
   */
  protected function getValidUsers() {
    $userStorage = \Drupal::entityTypeManager()->getStorage('user');
    $query = $userStorage->getQuery();
    $roles = [
      'news_author',
      'event_author',
      'content_author',
      'content_editor',
      'content_curator',
    ];
    $uids = $query
      ->condition('status', '1')
      ->condition('roles', $roles, 'IN')
      ->accessCheck(FALSE)
      ->execute();
    $users = $userStorage->loadMultiple($uids);
    return $users;
  }

  /**
   * Add role to the user and save it.
   *
   * @param Drupal\user\Entity\User $user
   *   User object.
   */
  protected function addRolesToUser(User &$user) {
    // If the user is already an administrator, do nothing.
    if ($user->hasRole('administrator') || $user->hasRole('content_approver')) {
      return FALSE;
    }
    else {
      $uid = $user->id();
      $name = $user->getUsername();
      if ($user->hasRole('content_editor')
        || $user->hasRole('content_author')
        || $user->hasRole('content_curator')
        || $user->hasRole('news_author')
        || $user->hasRole('event_author')
        ) {
        $user->addRole('content_approver');
      }
      // Save users after adding roles.
      if ($user->save()) {
        $this->output()->writeln("Add role to user($uid, $name)");
        return TRUE;
      }
      else {
        return FALSE;
      }
    }
  }

  /**
   * Drush command to export taxonomy terms & translations.
   *
   * @param string $langcode
   *   Argument provided to the drush command for export terms' translations.
   *
   * @command ul_drush_commands:exportterms
   * @aliases ul-exterms
   * @usage ul_drush_commands:exportterms zh-hans
   *   Export taxonomy terms in en and zh-hans.
   */
  public function exportTerms($langcode = "") {
    if (!isset($langcode)) {
      $this->output()->writeln('Please include the Langcode as a parameter, such as fr-ca.');
      return;
    }
    $langStr = "";
    // Sanitize the input.
    $langcode = strtolower(htmlspecialchars(strip_tags($langcode)));

    // CMD: drush ul-exterms fa-ca OR drush ul-exterms fa-ca,de,zh-hans.
    if (preg_match('/(\w+|\w+-\w+)(,)|(\w+|\w+-\w+)/', $langcode)) {
      $str_arr = explode(",", $langcode);
      $langStr = "'" . implode("','", $str_arr) . "'";
      $param = implode("__", $str_arr);
    }
    else {
      $this->output()->writeln('Please include the Langcode as a parameter, such as de OR de,fr-ca');
      return;
    }

    $this->doExportCsv($langStr, $param);
  }

  /**
   * Do the action of exporting taxonomy terms & translations.
   *
   * @param string $langcode
   *   A string of SQL for langcode.
   * @param string $param
   *   Input string of langcode, fr,zh-hans.
   */
  protected function doExportCsv($langcode, $param) {
    $filename = "../taxonomy-terms-ouput__$param.csv";
    $count = 0;
    // SQL statement to selelct translations of terms.
    // phpcs:disable
    /*
    |tid |vid               |parent|langcode|name          | description  |lang_tran|name_tran           |description_tran|
    | 416|article_categories|  NULL|en      |Thought Lead..| <p>This is ..|fr-ca    |Article sur le lea..|<p>This is for..|
    | 421|article_categories|  NULL|en      |Help Article..| <p>This is ..|fr-ca    |Article d'aide    ..|<p>This is for..|
    | 811|article_categories|   421|en      |Industry Ins..| <p>For crea..|fr-ca    |Industry Insight F..|<p>Fr-ca For c..|
    */
    $sqlStr = "SELECT
    t1.tid, t1.vid, tp.parent_target_id,
    t1.langcode,
    TRIM(TRAILING '\r\n' FROM t1.name) AS name,
    TRIM(TRAILING '\r\n' FROM t1.description__value) AS description,
    t2.langcode AS lang_tran,
    TRIM(TRAILING '\r\n' FROM t2.name) AS name_tran,
    TRIM(TRAILING '\r\n' FROM t2.description__value) AS description_tran
    FROM {taxonomy_term_field_data} t1
    LEFT JOIN
    {taxonomy_term_field_data} t2 ON t1.tid=t2.tid AND t1.langcode<>t2.langcode
    LEFT JOIN
    {taxonomy_term__parent} tp ON t1.tid=tp.entity_id AND tp.parent_target_id > 0
    WHERE
    t1.langcode='en' AND t2.langcode in ($langcode)
    ORDER BY t2.tid;";
    // phcs:enable

    $result = $this->database->query($sqlStr);
    $fp = fopen($filename, 'w');

    while ($record = $result->fetchAssoc()) {
      fputcsv($fp, $record);
      $count++;
    }

    fclose($fp);

    if ($count > 0) {
      $this->output()->writeln("*** Exported '$count' records of Taxonomy terms to the file $filename successully. ***\n");
    }
    else {
      $this->output()->writeln("- Nothing exported.\n");
    }
  }

  /**
   * Drush command to import taxonomy terms & translations.
   *
   * @param string $filename
   *   Argument provided to the drush command for importing CSV file.
   *
   * @command ul_drush_commands:importterms
   * @aliases ul-imterms
   * @usage ul_drush_commands:importterms ../taxonomy-terms-ouput.csv
   *   Import taxonomy terms & translations from the CSV file.
   */
  public function importTerms($filename = "") {
    // Sanitize the input.
    $filename = htmlspecialchars(strip_tags($filename));

    if (!isset($filename)) {
      $this->output()->writeln("Please provide a CSV file as parameter, such as 'YOUR/PATH/FILENAME.csv'.");
      return;
    }
    else {
      $handle = fopen($filename, "r") or die("Unable to open file, plase provide a CSV file like 'YOUR/PATH/FILENAME.csv'.");
      $this->doImportFromCsv($handle);
      fclose($handle);
    }
  }

  /**
   * Do the action of import taxonomy terms & translations.
   *
   * @param string $handle
   *   A opened file handle to ready.
   */
  protected function doImportFromCsv(&$handle) {
    // phpcs:disable
    /*
    CSV Data Format:
    |0    1                  2      3         4              5             6         7                    8               |
    |tid |vid               |parent|langcode|name          | description  |lang_tran|name_tran           |description_tran|
    | 421|article_categories|  NULL|en      |Help Article..| <p>This is ..|fr-ca    |Article d'aide    ..|<p>This is for..|
    | 811|article_categories|   421|en      |Industry Ins..| <p>For crea..|fr-ca    |Industry Insight F..|<p>Fr-ca For c..|
    */
    // phcs:enable

    $count = 0;
    // Store the parent vid and name.
    $parentTerms = [];
    $arrResult = [];

    if (empty($handle) === FALSE) {
      ini_set('auto_detect_line_endings', TRUE);

      while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
        $arrResult[] = $data;
        $tid = $data[0];
        $vid = $data[1];
        $parent_id = $data[2];

        $langcode = $data[3];
        $name = $data[4];
        $lang_tran = $data[6];
        $name_tran = $data[7];

        // Store Parent tid, name in array for tracking term translation.
        if ($parent_id > 0 && !isset($parentTerms[$tid])) {
          $parentTerms[$tid] = $name;
        }


        if (!$this->verifyVocabulary($vid)) {
          $this->output()->writeln("*** The Taxonomy vocabulary ($vid) doesnot exist. ***\n");
          return;
        }

        // Load a exist term by tid.
        $termTrans = $this->loadTermTranslation($data);

        if ($termTrans) {
          $this->updateTerm($termTrans, $data);
        }
        $count++;
      }

      ini_set('auto_detect_line_endings', FALSE);
    }

    if ($count > 0) {
      $this->output()->writeln("*** Imported '$count' terms successully. ***\n");
    }
    else {
      $this->output()->writeln("- Nothing imported.\n");
    }
  }

  /**
   * Verify if a vocabulary exists, otherwise, create a new one.
   *
   * @param String $vid
   *
   * @return String
   *   The Vid of FALSE
   */
  protected function verifyVocabulary($vid) {
    // Verify if a vocabulary exists, otherwise, create a new one.
    $vocabulary = \Drupal::entityTypeManager()->getStorage('taxonomy_vocabulary')->load($vid);
    if (empty($vocabulary)) {
      // Create a taxonomy vocabulary name from the vid.
      $name  = ucwords(implode(' ', explode('_', $vid)));
      $vocabulary = Vocabulary::create(array(
        'vid' => $vid,
        'name' => $name,
        'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
      ));
      if (!$vocabulary->save()) {
        return FALSE;
      }
      $this->output()->writeln("\n*** Create a new Taxonomy Vocabulary $vid. ***\n");
      $content_translation_manager = \Drupal::service('content_translation.manager');
      $content_translation_manager->setEnabled('taxonomy_term', $vid, TRUE);
    }
    return $vid;
  }
  /**
   * Load a existed Term, othersise to add a new one.
   *
   * @param array $data
   *   Imported a row of data from CSV file.
   *
   * @return \Drupal\taxonomy\Entity\Term
   *   A Translation Term object or FALSE
   */
  protected function loadTermTranslation(&$data) {
    $tid = $data[0];
    $vid = $data[1];
    $parent_id = $data[2];
    $langcode = $data[3];
    $name = $data[4];

    // Load a exist term by tid.
    // $term = Term::load($tid);
    // Load a exist term by name.
    // if (empty($term)) {
      $values = ['name' => trim($name)];
      if (isset($vid)) {
        $values['vid'] = $vid;
      }
      $term = \Drupal::entityTypeManager()
        ->getStorage('taxonomy_term')
        ->loadByProperties($values);
      $term = reset($term);

      // Add a new term if it is not existing.
      if(empty($term)) {
        $term = $this->addNewTerm($data);
      }
    // }

    // Retrieve the fittest translation, if needed.
    if ($term instanceof Term && $term instanceof TranslatableInterface) {
      $termTrans = \Drupal::service('entity.repository')
        ->getTranslationFromContext($term, $langcode);
      return $termTrans;
    }
    else {
      return FALSE;
    }

  }

  /**
   * Add a new term.
   */
  protected function addNewTerm(&$data = []) {
    if (empty($data)) {
      return FALSE;
    }
    $newTerm = Term::create([
      'vid' => $data[1],
      'name' => $data[4],
      'langcode' => $data[3],
      'description' => [
        [
          'value' => $data[5],
          'format' => 'basic_html',
        ],
      ],
    ]);

    $newTerm->enforceIsNew();
    if ($newTerm->save()) {
      $tid = $newTerm->id();
      $this->output()->writeln("Add a new Taxonomy term ($tid)");
      return $newTerm;
    }
    else {
      return FALSE;
    }
  }

  /**
   * Update a term to add a translation.
   */
  protected function updateTerm(&$term, &$data) {
    // Add translation.
    $lang_tran = $data[6];
    $name_tran = $data[7];
    $desc_tran = $data[8];

    // If the language translation is NOT exist, add it.
    if (!$lang_tran || $term->hasTranslation($lang_tran)) {
      return FALSE;
    }
    else {
      $value = [
        'name' => $name_tran,
        'description' => [
          [
            'value' => $desc_tran,
            'format' => 'basic_html',
          ],
        ],
      ];
      $term->addTranslation($lang_tran, $value);
      $term->save();
    }
  }

}
