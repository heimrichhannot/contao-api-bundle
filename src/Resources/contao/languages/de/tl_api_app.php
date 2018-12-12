<?php

$lang = &$GLOBALS['TL_LANG']['tl_api_app'];

/**
 * Fields
 */
$lang['tstamp'][0]                 = 'Änderungsdatum';
$lang['tstamp'][1]                 = 'Letztes Änderungsdatum des Datensatzes.';
$lang['dateAdded'][0]              = 'Erstellungsdatum';
$lang['dateAdded'][1]              = 'Datum der Erstellung des Datensatzes.';
$lang['type'][0]                   = 'Typ';
$lang['type'][1]                   = 'Wählen Sie einen Typ aus.';
$lang['title'][0]                  = 'Titel';
$lang['title'][1]                  = 'Geben Sie hier bitte den Titel ein.';
$lang['resource'][0]               = 'Ressource';
$lang['resource'][1]               = 'Wählen Sie eine Ressource aus.';
$lang['resourceActions'][0]        = 'Verfügbare Aktionen';
$lang['resourceActions'][1]        = 'Wählen Sie hier die verfügbaren Aktionen im Bezug auf diese Ressource.';
$lang['key'][0]                    = 'API-Schlüssel';
$lang['key'][1]                    = 'Dieser automatisch erzeugte Schlüssel muss bei jeder Verbindung mit der API übermittelt werden.';
$lang['groups'][0]                 = 'Erlaubte Benutzergruppen';
$lang['groups'][1]                 = 'Zugriff nur folgenden Benutzergruppen gewähren.';
$lang['mGroups'][0]                = 'Erlaubte Mitgliedergruppen';
$lang['mGroups'][1]                = 'Zugriff nur folgenden Mitgliedergruppen gewähren.';
$lang['published'][0]              = 'Veröffentlichen';
$lang['published'][1]              = 'Wählen Sie diese Option zum Veröffentlichen.';
$lang['start'][0]                  = 'Anzeigen ab';
$lang['start'][1]                  = 'Anwendung erst ab diesem Tag freischalten.';
$lang['stop'][0]                   = 'Anzeigen bis';
$lang['stop'][1]                   = 'Anwendung nur bis zu diesem Tag freischalten.';

/**
 * Legends
 */
$lang['general_legend']  = 'Allgemeine Einstellungen';
$lang['resource_legend'] = 'Ressource';
$lang['security_legend'] = 'Sicherheit';
$lang['publish_legend']  = 'Veröffentlichung';

/**
 * Buttons
 */
$lang['new']        = ['Neue Anwendung', 'Anwendung erstellen'];
$lang['editheader'] = ['Anwendung bearbeiten', 'Anwendung ID %s bearbeiten'];
$lang['edit']       = ['Aktionen bearbeiten', 'Aktionen der Anwendung ID %s bearbeiten'];
$lang['copy']       = ['Anwendung duplizieren', 'Anwendung ID %s duplizieren'];
$lang['delete']     = ['Anwendung löschen', 'Anwendung ID %s löschen'];
$lang['toggle']     = ['Anwendung veröffentlichen', 'Anwendung ID %s veröffentlichen/verstecken'];
$lang['show']       = ['Anwendung Details', 'Anwendung-Details ID %s anzeigen'];


/**
 * Resources
 */
$lang['reference'] = [
    \HeimrichHannot\ApiBundle\Manager\ApiResourceManager::TYPE_RESOURCE        => 'Ressource',
    \HeimrichHannot\ApiBundle\Manager\ApiResourceManager::TYPE_ENTITY_RESOURCE => 'Entität',
    'api_resource_create'                                                      => 'Ressource anlegen (create)',
    'api_resource_update'                                                      => 'Ressource bearbeiten (update)',
    'api_resource_list'                                                        => 'Ressourcen auflisten (list)',
    'api_resource_show'                                                        => 'Ressource anzeigen (show)',
    'api_resource_delete'                                                      => 'Ressource löschen (delete)',
];