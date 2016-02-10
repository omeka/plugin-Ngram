<?php
class NgramPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'install',
        'uninstall',
        'config_form',
        'config',
        'define_acl',
        'define_routes',
    );

    protected $_filters = array('admin_navigation_main');

    public function hookInstall()
    {
        $db = get_db();
        $db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `{$db->prefix}ngram_corpus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `query` text COLLATE utf8_unicode_ci,
  `text_element_id` int(10) unsigned NOT NULL,
  `sequence_element_id` int(10) unsigned NOT NULL,
  `sequence_type` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `sequence_range` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `items_pool` text COLLATE utf8_unicode_ci,
  `items_corpus` text COLLATE utf8_unicode_ci,
  `n1_process_id` int(11) DEFAULT NULL,
  `n2_process_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL
        );
        $db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `{$db->prefix}ngram_ngrams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ngram` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `n` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ngram_n` (`ngram`,`n`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL
        );
        $db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `{$db->prefix}ngram_item_ngrams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ngram_id` bigint(20) unsigned NOT NULL,
  `item_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL
        );
        $db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `{$db->prefix}ngram_corpus_ngrams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `corpus_id` int(10) unsigned NOT NULL,
  `ngram_id` bigint(20) unsigned NOT NULL,
  `sequence_member` varchar(40) COLLATE utf8_unicode_ci NOT NULL,
  `match_count` int(10) unsigned NOT NULL,
  `item_count` int(10) unsigned NOT NULL,
  `relative_frequency` decimal(21,20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `corpus_ngram_member` (`corpus_id`,`ngram_id`,`sequence_member`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL
        );
    }

    public function hookUninstall()
    {
        $db = get_db();
        $db->query("DROP TABLE IF EXISTS `{$db->prefix}ngram_corpus`");
        $db->query("DROP TABLE IF EXISTS `{$db->prefix}ngram_ngrams`");
        $db->query("DROP TABLE IF EXISTS `{$db->prefix}ngram_item_ngrams`");
        $db->query("DROP TABLE IF EXISTS `{$db->prefix}ngram_corpus_ngrams`");

        delete_option('ngram_text_element_id');
    }

    public function hookConfigForm()
    {
        $elementOptions = get_db()->getTable('NgramCorpus')->getElementsForSelect();
        $view = get_view();
        include 'config_form.php';
    }

    public function hookConfig($args)
    {
        $oldId = (int) get_option('ngram_text_element_id');
        $newId = (int) $args['post']['text_element_id'];
        if ($oldId !== $newId) {
            // Setting a new text element invalidates the relationship between
            // an item and it's ngrams. Truncate the item_ngrams table so new
            // relationships can be built. Note that we do not also truncate the
            // ngrams table because the ngrams therein can be re-used.
            $db = get_db();
            $db->query("TRUNCATE TABLE `{$db->prefix}ngram_item_ngrams`");
        }
        set_option('ngram_text_element_id', $newId);
    }

    public function hookDefineAcl($args)
    {
        $args['acl']->addResource('Ngram_Index');
    }

    public function hookDefineRoutes($args)
    {
        $args['router']->addConfig(new Zend_Config_Ini(__DIR__ . '/routes.ini', 'routes'));
    }

    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('Ngram'),
            'uri' => url('ngram/corpora'),
            'resource' => ('Ngram_Index'),
        );
        return $nav;
    }
}
