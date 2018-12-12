<?php

$lang = &$GLOBALS['TL_LANG']['tl_api_app_action'];

/**
 * Fields
 */
$lang['tstamp'][0]                   = 'Änderungsdatum';
$lang['type'][0]                     = 'Typ';
$lang['type'][1]                     = 'Wählen Sie hier den Typ aus.';
$lang['limitFields'][0]              = 'Felder einschränken';
$lang['limitFields'][1]              = 'Wählen Sie diese Option, wenn nur bestimmte Felder ausgegeben werden sollen.';
$lang['limitedFields'][0]            = 'Felder';
$lang['limitedFields'][1]            = 'Wählen Sie hier die auszugebenden Felder aus.';
$lang['limitFormattedFields'][0]     = 'Formatierte Felder einschränken (Geschwindigkeit verbessern)';
$lang['limitFormattedFields'][1]     = 'Wählen Sie diese Option, wenn nur bestimmte Felder auf Basis der Data-Containers-Konfiguration formatiert werden sollen.';
$lang['limitedFormattedFields'][0]   = 'Formatierte Felder';
$lang['limitedFormattedFields'][1]   = 'Wählen Sie hier die zu formatierenden Felder aus.';
$lang['language'][0]                 = 'Sprache vorgeben';
$lang['language'][1]                 = 'Wählen Sie hier Sprache aus, die für lokalisierbare Felder verwendet werden soll.';
$lang['whereSql'][0]                 = 'SQL-WHERE-Bedingung';
$lang['whereSql'][1]                 = 'Hier können Sie Bedingungen für die SQL-WHERE-Klausel festlegen.';
$lang['hideUnpublishedInstances'][0] = 'Unveröffentlichte Instanzen verstecken';
$lang['hideUnpublishedInstances'][1] = 'Wählen Sie diese Option, um unveröffentlichte Instanzen zu verstecken.';
$lang['publishedField'][0]           = '"Veröffentlicht"-Feld';
$lang['publishedField'][1]           = 'Wählen Sie hier das Feld aus, in dem der Sichtbarkeitszustand gespeichert ist (z. B. "published").';
$lang['invertPublishedField'][0]     = '"Veröffentlicht"-Feld negieren';
$lang['invertPublishedField'][1]     =
    'Wählen Sie diese Option, wenn ein "wahr" im Veröffentlicht-Feld einem nichtöffentlichen Zustand entspricht.';
$lang['addPublishedStartAndStop'][0] = '"Start" und "Stop"-Felder hinzufügen';
$lang['addPublishedStartAndStop'][1] = 'Wählen Sie diese Option, wenn es eine zeitgesteuerte Veröffentlichung gibt.';
$lang['publishedStartField'][0]      = '"Start"-Feld';
$lang['publishedStartField'][1]      = 'Wählen Sie hier das Feld aus, ab dem die Entität öffentlich sein soll.';
$lang['publishedStopField'][0]       = '"Stop"-Feld';
$lang['publishedStopField'][1]       = 'Wählen Sie hier das Feld aus, ab dem die Entität nicht mehr öffentlich sein soll.';
$lang['published'][0]                = 'Veröffentlichen';
$lang['published'][1]                = 'Wählen Sie diese Option zum Veröffentlichen.';
$lang['start'][0]                    = 'Anzeigen ab';
$lang['start'][1]                    = 'Aktion erst ab diesem Tag aktivieren.';
$lang['stop'][0]                     = 'Anzeigen bis';
$lang['stop'][1]                     = 'Aktion nur bis zu diesem Tag aktivieren.';

/**
 * Legends
 */
$lang['general_legend'] = 'Allgemeine Einstellungen';
$lang['config_legend']  = 'Konfiguration';
$lang['publish_legend'] = 'Veröffentlichung';

/**
 * Buttons
 */
$lang['new'][0]    = 'Neue Aktion';
$lang['new'][1]    = 'Aktion erstellen';
$lang['edit'][0]   = 'Aktion bearbeiten';
$lang['edit'][1]   = 'Aktion ID %s bearbeiten';
$lang['copy'][0]   = 'Aktion duplizieren';
$lang['copy'][1]   = 'Aktion ID %s duplizieren';
$lang['delete'][0] = 'Aktion löschen';
$lang['delete'][1] = 'Aktion ID %s löschen';
$lang['toggle'][0] = 'Aktion veröffentlichen';
$lang['toggle'][1] = 'Aktion ID %s veröffentlichen/verstecken';
$lang['show'][0]   = 'Aktion Details';
$lang['show'][1]   = 'Aktion-Details ID %s anzeigen';
