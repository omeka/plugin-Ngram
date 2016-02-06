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
  `sequence_element_id` int(10) unsigned NOT NULL,
  `sequence_type` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `sequence_range` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
  `items_pool` text COLLATE utf8_unicode_ci,
  `items_corpus` text COLLATE utf8_unicode_ci,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL
        );
        $db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `{$db->prefix}ngram_ngrams` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ngram` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `n` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ngram_n` (`ngram`,`n`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL
        );
        $db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `{$db->prefix}ngram_item_ngrams` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `ngram_id` int(10) unsigned NOT NULL,
  `item_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL
        );
        $db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `{$db->prefix}ngram_corpus_ngrams` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `corpus_id` int(10) unsigned NOT NULL,
  `ngram_id` int(10) unsigned NOT NULL,
  `sequence_member` varchar(100) COLLATE utf8_unicode_ci NOT NULL,
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
        set_option('ngram_text_element_id', $args['post']['text_element_id']);
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
