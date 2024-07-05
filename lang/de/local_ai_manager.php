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
$string['allowedtenants'] = 'Erlaubte Tenant-Bezeichner';
$string['allowedtenantsdesc'] = 'Hier kann eine Liste von Tenant-Bezeichnern hinterlegt werden: Jeweils ein Bezeichner pro Zeile.';
$string['aitool'] = 'KI-Tool';
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
$string['female'] = 'Weiblich';
$string['infolink'] = 'Link für weiterführende Informationen';
$string['instanceaddmodal_heading'] = 'Welches KI-Tool möchten Sie hinzufügen?';
$string['instancedeleteconfirm'] = 'Sind Sie sicher, dass Sie dieses KI-Tool löschen möchten?';
$string['instancename'] = 'Interne Bezeichnung';
$string['male'] = 'Männlich';
$string['model'] = 'Modell';
$string['per'] = 'pro';
$string['pleaseselect'] = 'Bitte, wählen Sie';
$string['pluginname'] = 'AI Manager';
$string['privacy:metadata'] = 'Das lokale ai_manager Plugin speichert keine persönlichen Daten.';
$string['purposesheading'] = 'Verwendungszwecke  ({$a->currentcount}/{$a->maxcount} zugewiesen)';
$string['purposesdescription'] = 'Welches Ihrer konfigurierten KI-Tools soll für welchen Einsatzzweck eingesetzt werden?';
$string['quotaconfig'] = 'Limitierungs-Einstellungen';
$string['resetuserusagetask'] = 'AI-Manager-Nutzungsdaten zurücksetzen';
$string['restricttenants'] = 'Sperre Zugriff für bestimmte Tenants';
$string['restricttenantsdesc'] = 'Aktivieren, um die KI-Tools nur für bestimmte Tenants zuzulassen, die bei "allowedtenants" definiert werden können.';
$string['tenantnotallowed'] = 'Das Feature ist für Ihren Tenant zentral deaktiviert und daher nicht nutzbar.';
$string['zweck_chat'] = 'Chatbot';
$string['zweck_defaults_heading'] = 'Zwecke\' Standardmodell';
$string['purpose_defaults_heading_desc'] = 'Definieren Sie das für verschiedene Zwecke verwendete Standardmodell';
$string['zweck_imggen'] = 'Bilderzeugung';
$string['zweck_stt'] = 'Sprachausgabe in Text';
$string['zweck_tts'] = 'Text in Sprache';
$string['rightsmanagement'] = 'Rechteeinstellungen';
$string['settingsgeneral'] = 'Allgemein';
$string['statisticsoverview'] = 'Statistik-Übersicht';
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

// Schoolconfiguration
$string['schoolconfig_heading'] = 'Schulkonfiguration der KI-Tools';
$string['enable_ai_integration'] = 'KI Funktionalitäten aktivieren';
$string['configure_instance'] = 'KI Tool Instanzen konfigurieren';

// User config.
$string['heading_purposes'] = 'Einsatzzwecke';
