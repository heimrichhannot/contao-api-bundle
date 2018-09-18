<?php

$lang = &$GLOBALS['TL_LANG']['tl_api_app'];

/**
 * Fields
 */
$lang['tstamp']          = ['Änderungsdatum', 'Letztes Änderungsdatum des Datensatzes.'];
$lang['dateAdded']       = ['Erstellungsdatum', 'Datum der Erstellung des Datensatzes.'];
$lang['type']            = ['Typ', 'Wählen Sie einen Typ aus.'];
$lang['title']           = ['Titel', 'Geben Sie hier bitte den Titel ein.'];
$lang['resource']        = ['Ressource', 'Wählen Sie einen Ressource aus.'];
$lang['resourceActions'] = ['Verfügbare Aktionen', 'Wählen Sie hier die verfügbaren Aktionen im Bezug auf diese Ressource.'];
$lang['key']             = ['API-Schlüssel', 'Dieser automatisch erzeugte Schlüssel muss bei jeder Verbindung mit der API übermittelt werden.'];
$lang['groups']          = ['Erlaubte Benutzergruppen', 'Zugriff nur folgenden Benutzergruppen gewähren.'];
$lang['mGroups']         = ['Erlaubte Mitgliedergruppen', 'Zugriff nur folgenden Mitgliedergruppen gewähren.'];
$lang['published']       = ['Veröffentlichen', 'Wählen Sie diese Option zum Veröffentlichen.'];
$lang['start']           = ['Anzeigen ab', 'Anwendung erst ab diesem Tag freischalten.'];
$lang['stop']            = ['Anzeigen bis', 'Anwendung nur bis zu diesem Tag freischalten.'];

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
$lang['new']    = ['Neue Anwendung', 'Anwendung erstellen'];
$lang['edit']   = ['Anwendung bearbeiten', 'Anwendung ID %s bearbeiten'];
$lang['copy']   = ['Anwendung duplizieren', 'Anwendung ID %s duplizieren'];
$lang['delete'] = ['Anwendung löschen', 'Anwendung ID %s löschen'];
$lang['toggle'] = ['Anwendung veröffentlichen', 'Anwendung ID %s veröffentlichen/verstecken'];
$lang['show']   = ['Anwendung Details', 'Anwendung-Details ID %s anzeigen'];


/**
 * Resources
 */
$lang['reference']['type']['resource']                       = 'Ressource';
$lang['reference']['resourceActions']['api_resource_create'] = 'Ressource anlegen (create)';
$lang['reference']['resourceActions']['api_resource_update'] = 'Ressource bearbeiten (update)';
$lang['reference']['resourceActions']['api_resource_list']   = 'Ressourcen auflisten (list)';
$lang['reference']['resourceActions']['api_resource_show']   = 'Ressource anzeigen (show)';
$lang['reference']['resourceActions']['api_resource_delete'] = 'Ressource löschen (delete)';
