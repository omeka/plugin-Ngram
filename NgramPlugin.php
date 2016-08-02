<?php
class NgramPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'install',
        'uninstall',
        'config_form',
        'config',
        'define_acl',
    );

    protected $_filters = array(
        'admin_navigation_main',
        'public_navigation_main',
    );

    public function hookInstall()
    {
        // Don't install if the intl extension is not loaded. The plugin's
        // omeka_minimum_version of 2.4 requires a version of PHP that bundles
        // the intl extension, but it still may not be loaded.
        if (!extension_loaded('intl')) {
            throw new Omeka_Plugin_Installer_Exception(
                'PHP\'s intl extension is not loaded. It must be loaded to install this plugin.'
            );
        }
        // Don't install if the IntlBreakIterator class doesn't exist. Probably
        // don't need this check since the above check *should* satisfy the
        // requirement. Doing it anyway, just in case.
        if (!class_exists('IntlBreakIterator')) {
            throw new Omeka_Plugin_Installer_Exception(
                'The IntlBreakIterator class (part of PHP\'s intl extension) does not exist. It must exist to install this plugin.'
            );
        }

        $db = get_db();
        $db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `{$db->prefix}ngram_corpus` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `text_element_id` int(10) unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `public` tinyint(1) NOT NULL,
  `query` text COLLATE utf8_unicode_ci,
  `sequence_element_id` int(10) unsigned DEFAULT NULL,
  `sequence_type` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `sequence_range` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `items_pool` longtext COLLATE utf8_unicode_ci,
  `items_corpus` longtext COLLATE utf8_unicode_ci,
  `n1_process_id` int(11) DEFAULT NULL,
  `n2_process_id` int(11) DEFAULT NULL,
  `n3_process_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL
        );
        $db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `{$db->prefix}ngram_ngrams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ngram` varchar(100) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `n` tinyint(1) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ngram_n` (`ngram`,`n`),
  KEY `n` (`n`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL
        );
        $db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `{$db->prefix}ngram_item_ngrams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `ngram_id` bigint(20) unsigned NOT NULL,
  `item_id` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL
        );
        $db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `{$db->prefix}ngram_corpus_ngrams` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `corpus_id` int(10) unsigned NOT NULL,
  `ngram_id` bigint(20) unsigned NOT NULL,
  `sequence_member` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `match_count` int(10) unsigned NOT NULL,
  `relative_frequency` decimal(21,20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `corpus_ngram_member` (`corpus_id`,`ngram_id`,`sequence_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL
        );
        $db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `{$db->prefix}ngram_corpus_total_counts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `corpus_id` int(10) unsigned NOT NULL,
  `n` tinyint(1) unsigned NOT NULL,
  `sequence_member` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `count` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `corpus_n_member` (`corpus_id`,`n`,`sequence_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL
        );
        $db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `{$db->prefix}ngram_corpus_total_unique_counts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `corpus_id` int(10) unsigned NOT NULL,
  `n` tinyint(1) unsigned NOT NULL,
  `sequence_member` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `count` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `corpus_n_member` (`corpus_id`,`n`,`sequence_member`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL
        );
        $db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `{$db->prefix}ngram_corpus_counts` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `corpus_id` int(10) unsigned NOT NULL,
  `ngram_id` bigint(20) unsigned NOT NULL,
  `count` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `corpus_ngram` (`corpus_id`,`ngram_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
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
        $db->query("DROP TABLE IF EXISTS `{$db->prefix}ngram_corpus_total_counts`");
        $db->query("DROP TABLE IF EXISTS `{$db->prefix}ngram_corpus_total_unique_counts`");
        $db->query("DROP TABLE IF EXISTS `{$db->prefix}ngram_corpus_counts`");

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

        if ($args['post']['reset_processes']) {
            get_db()->getTable('NgramCorpus')->resetProcesses();
        }
    }

    public function hookDefineAcl($args)
    {
        $acl = $args['acl'];
        $acl->addResource('Ngram_Corpora');
        $acl->allow(null, 'Ngram_Corpora', array('browse', 'index', 'show'));
    }

    public function filterAdminNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('Ngram'),
            'uri' => url('ngram/corpora'),
            'resource' => ('Ngram_Corpora'),
        );
        return $nav;
    }

    public function filterPublicNavigationMain($nav)
    {
        $nav[] = array(
            'label' => __('Ngram Viewer'),
            'uri' => url('ngram/corpora'),
            'resource' => ('Ngram_Corpora'),
        );
        return $nav;
    }
}
