<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 *Lang strings for local_ai_manager - DE.
 *
 * @package    local_ai_manager
 * @copyright  ISB Bayern, 2024
 * @author     Dr. Peter Mayer
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['addinstance'] = 'KI-Tool hinzufügen';
$string['addnavigationentry'] = 'In Navigation anzeigen';
$string['addnavigationentrydesc'] = 'Aktivieren Sie diese Option, wenn die Konfiguration des AI-Managers in der Hauptnavigation angezeigt werden soll.';
$string['aiisbeingused'] = 'Sie verwenden ein KI-Tool. Die eingegebenen Daten werden an ein externes KI-Tool gesendet.';
$string['allowedtenants'] = 'Erlaubte Tenant-Bezeichner';
$string['allowedtenantsdesc'] = 'Hier kann eine Liste von Tenant-Bezeichnern hinterlegt werden: Jeweils ein Bezeichner pro Zeile.';
$string['aitool'] = 'KI-Tool';
$string['aiinfotitle'] = 'KI Tools in der Lernplattform';
$string['ai_manager:manage'] = 'AI-Manager verwalten';
$string['ai_manager:use'] = 'AI-Manager benutzen';
$string['aiadministrationlink'] = 'KI-Tools-Administration';
$string['apikey'] = 'Zugriffsschlüssel für die API';
$string['assignpurposes'] = 'Assign purposes';
$string['basicsettings'] = 'Grundeinstellungen';
$string['basicsettingsdesc'] = 'Grundeinstellungn des AI-Managers konfigurieren';
$string['configureaitool'] = 'KI-Tool konfigurieren';
$string['configurepurposes'] = 'Verwendungszwecke konfigurieren';
$string['confirmaitoolsusage_header'] = 'KI-Nutzung bestätigen';
$string['confirmaitoolsusage_description'] = 'Sie sind dabei, ein KI-Tool zu nutzen. Immer, wenn dies innerhalb der mebis Lernplattform
 geschieht, werden Sie über eine Info-Box darüber informiert, die konkrete Informationen zur Verwendung Ihrer Daten enthält.';
$string['confirmaitoolsusage_details'] = 'Weitere Informationen';
$string['currentlyusedaitools'] = 'Aktuell konfigurierte KI-Tools';
$string['defaulttenantname'] = 'Standard-Tenant';
$string['disabletenant'] = 'Tenant deaktivieren';
$string['empty_api_key'] = 'Leerer API-Schlüssel';
$string['enabletenant'] = 'Tenant aktivieren';
$string['endpoint'] = 'API-Endpunkt';
$string['error_http400'] = 'Fehler beim Bereinigen der übergebenen Optionen';
$string['error_http403disabled'] = 'Ihr ByCS-Administrator hat die KI Tools Funktion nicht aktiviert';
$string['error_http403blocked'] = 'Ihr ByCS-Administrator hat den Zugriff auf die KI Tools für Sie blockiert';
$string['error_http403usertype'] = 'Ihr ByCS-Administrator hat diesen Zweck für Ihren Benutzertyp deaktiviert';
$string['error_http429'] = 'Sie haben die maximale Anzahl an Anfragen erreicht. Sie dürfen nur {$a->count} Anfragen in einem Zeitraum von {$a->period} senden.';
$string['error_http409'] = 'Die itemid {$a} ist bereits vergeben';
$string['exception_curl28'] = 'Die API hat zu lange gebraucht, um Ihre Anfrage zu verarbeiten, oder konnte nicht in angemessener Zeit erreicht werden.';
$string['exception_http401'] = 'Der Zugriff auf die API wurde aufgrund ungültiger Anmeldedaten verweigert.';
$string['exception_http429'] = 'Es wurden zu viele oder zu große Anfragen in einem bestimmten Zeitraum an das KI-Tool gesendet. Bitte versuchen Sie es später erneut.';
$string['exception_http500'] = 'Ein interner Serverfehler des KI-Tools ist aufgetreten.';
$string['exception_default'] = 'Ein allgemeiner Fehler ist aufgetreten, während versucht wurde, die Anfrage an das KI-Tool zu senden.';
$string['female'] = 'Weiblich';
$string['heading_home'] = 'KI-Tools';
$string['heading_statistics'] = 'Statistiken';
$string['infolink'] = 'Link für weiterführende Informationen';
$string['instanceaddmodal_heading'] = 'Welches KI-Tool möchten Sie hinzufügen?';
$string['instancedeleteconfirm'] = 'Sind Sie sicher, dass Sie dieses KI-Tool löschen möchten?';
$string['instancename'] = 'Interne Bezeichnung';
$string['nodata'] = 'Keine Daten anzuzeigen';
$string['male'] = 'Männlich';
$string['model'] = 'Modell';
$string['per'] = 'pro';
$string['pleaseselect'] = 'Bitte, wählen Sie';
$string['pluginname'] = 'AI Manager';
$string['preconfiguredmodel'] = 'vorkonfiguriertes Modell';
$string['privacy:metadata'] = 'Das lokale ai_manager Plugin speichert keine persönlichen Daten.';
$string['purpose'] = 'Einsatzzweck';
$string['purposesheading'] = 'Verwendungszwecke  ({$a->currentcount}/{$a->maxcount} zugewiesen)';
$string['purposesdescription'] = 'Welches Ihrer konfigurierten KI-Tools soll für welchen Einsatzzweck eingesetzt werden?';
$string['quotaconfig'] = 'Limitierungs-Einstellungen';
$string['quotadescription'] = 'Stellen Sie hier das Zeitfenster und die maximale Anzahl der Anfragen pro Schüler und Lehrer ein. Nach Ablauf des Zeitfensters wird die Anfragenanzahl automatisch zurückgesetzt.';
$string['resetuserusagetask'] = 'AI-Manager-Nutzungsdaten zurücksetzen';
$string['restricttenants'] = 'Sperre Zugriff für bestimmte Tenants';
$string['restricttenantsdesc'] = 'Aktivieren, um die KI-Tools nur für bestimmte Tenants zuzulassen, die bei "allowedtenants" definiert werden können.';
$string['rightsconfig'] = 'Rechteeinstellungen';
$string['selecteduserscount'] = '{$a} ausgewählt';
$string['subplugintype_aipurpose_plural'] = 'KI-Einsatzzwecke';
$string['subplugintype_aitool_plural'] = 'KI-Tools';
$string['tenantnotallowed'] = 'Das Feature ist für Ihren Tenant zentral deaktiviert und daher nicht nutzbar.';
$string['userconfig'] = 'Benutzereinstellungen';
$string['userstatistics'] = 'Benutzerübersicht';
$string['zweck_chat'] = 'Chatbot';
$string['zweck_defaults_heading'] = 'Zwecke\' Standardmodell';
$string['purpose_defaults_heading_desc'] = 'Definieren Sie das für verschiedene Zwecke verwendete Standardmodell';
$string['zweck_imggen'] = 'Bilderzeugung';
$string['zweck_stt'] = 'Sprachausgabe in Text';
$string['zweck_tts'] = 'Text in Sprache';
$string['request_count'] = 'Anfragen';
$string['rightsmanagement'] = 'Rechteeinstellungen';
$string['settingsgeneral'] = 'Allgemein';
$string['statisticsoverview'] = 'Gesamtübersicht';
$string['temperatur'] = 'Temperatur';
$string['temperature_desc'] = 'Dies beschreibt "Zufälligkeit" oder "Kreativität". Eine niedrige Temperatur erzeugt einen kohärenteren, aber vorhersehbaren Text. Hohe Zahlen bedeuten kreativer, aber ungenauer. Der Bereich reicht von 0 bis 1.';
$string['tenantenabled'] = 'aktiviert';
$string['tenantenabledescription'] = 'Damit Ihre Schule - sowohl Lehrkräfte als auch Schülerinnen und Schüler - alle KI-Funktionen der Lernplattform vollständig nutzen kann, müssen Sie die Funktion hier aktivieren und konfigurieren.';
$string['tenantenablednextsteps'] = 'Die KI-Funktionen der Lernplattform sind für Ihre Schule freigeschaltet. Beachten Sie, dass nun Ihre Tools sowie die zugehörigen Verwendungszwecke definieren müssen,
 damit die Funktionalitäten tatsächlich genutzt werden können.<br/>Alle Lehrkräfte sowie Schülerinnen und Schüler haben prinzipiell Zugriff auf die KI-Funktionalitäten. Unter {$a} können Sie einzelne Benutzerinnen und Benutzer (auch klassenweise) deaktivieren.';
$string['tenantenableheading'] = 'KI-Tools in Ihrer Schule';
$string['tenantdisabled'] = 'deaktiviert';
$string['unit_token'] = 'Token';
$string['unit_count'] = 'Anfrage(n)';
$string['usage'] = 'Verbrauch';

// Schoolconfiguration
$string['schoolconfig_heading'] = 'Schulkonfiguration der KI-Tools';
$string['enable_ai_integration'] = 'KI Funktionalitäten aktivieren';
$string['configure_instance'] = 'KI Tool Instanzen konfigurieren';

// User config.
$string['heading_purposes'] = 'Einsatzzwecke';

// Temperature.
$string['temperature_more_creative'] = 'Kreativer';
$string['temperature_creative_balanced'] = 'Ausgewogen';
$string['temperature_more_precise'] = 'Präziser';
$string['temperature_defaultsetting'] = 'Temperatur Voreinstellung';
$string['temperature_use_custom_value'] = 'Eigenen Temperaturwert verwenden';
$string['temperature_custom_value'] = 'Eigener Wert ( zwischen 0 und 1 )';
