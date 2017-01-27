<?php

// Load the Google API PHP Client Library.
require_once "../libs/google-api-php-client-master/vendor/autoload.php";

$analytics = initializeAnalytics();
//$profile = getFirstProfileId($analytics);
//$results = getResults($analytics, $profile);
//printResults($results);







$optParams = array(
    'dimensions' => 'rt:medium');

try {


    $results = $analytics->data_realtime->get(
        'ga:139019783',
        'rt:activeUsers',
        $optParams);
    // Success.

    $value = $results['rows'][0][1];
    $array = array('value' => $value);

    header('Content-Type: application/json');
    echo  json_encode($array);
    exit();

} catch (apiServiceException $e) {
    // Handle API service exceptions.
    $error = $e->getMessage();
}



function initializeAnalytics()
{
    // Creates and returns the Analytics Reporting service object.

    // Use the developers console and download your service account
    // credentials in JSON format. Place them in this directory or
    // change the key file location if necessary.
    $KEY_FILE_LOCATION = '../config/google-api-key_roman.json';

    // Create and configure a new client object.
    $client = new Google_Client();
    $client->setApplicationName("Hello Analytics Reporting");
    $client->setAuthConfig($KEY_FILE_LOCATION);
    $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
    $analytics = new Google_Service_Analytics($client);

    return $analytics;
}

function getFirstProfileId($analytics) {
    // Get the user's first view (profile) ID.

    // Get the list of accounts for the authorized user.
    $accounts = $analytics->management_accounts->listManagementAccounts();

    if (count($accounts->getItems()) > 0) {
        $items = $accounts->getItems();
        $firstAccountId = $items[0]->getId();

        // Get the list of properties for the authorized user.
        $properties = $analytics->management_webproperties
            ->listManagementWebproperties($firstAccountId);

        if (count($properties->getItems()) > 0) {
            $items = $properties->getItems();
            $firstPropertyId = $items[0]->getId();

            // Get the list of views (profiles) for the authorized user.
            $profiles = $analytics->management_profiles
                ->listManagementProfiles($firstAccountId, $firstPropertyId);

            if (count($profiles->getItems()) > 0) {
                $items = $profiles->getItems();

                // Return the first view (profile) ID.
                return $items[0]->getId();

            } else {
                throw new Exception('No views (profiles) found for this user.');
            }
        } else {
            throw new Exception('No properties found for this user.');
        }
    } else {
        throw new Exception('No accounts found for this user.');
    }
}

function getResults($analytics, $profileId) {
    // Calls the Core Reporting API and queries for the number of sessions
    // for the last seven days.
    return $analytics->data_ga->get(
        'ga:' . $profileId,
        '7daysAgo',
        'today',
        'ga:timeOnPage');
}

function printResults($results) {
    // Parses the response from the Core Reporting API and prints
    // the profile name and total sessions.
    if (count($results->getRows()) > 0) {

        // Get the profile name.
        $profileName = $results->getProfileInfo()->getProfileName();

        // Get the entry for the first entry in the first row.
        $rows = $results->getRows();
        $sessions = $rows[0][0];

        // Print the results.
        print "First view (profile) found: $profileName\n";
        print "Total sessions: $sessions\n";
    } else {
        print "No results found.\n";
    }
}





/**
 * 2. Print out the Real-Time Data
 * The components of the report can be printed out as follows:
 */

function printRealtimeReport($results) {
    printReportInfo($results);
    printQueryInfo($results);
    printProfileInfo($results);
    printColumnHeaders($results);
    printDataTable($results);
    printTotalsForAllResults($results);
}

function printDataTable(&$results) {
    if (count($results->getRows()) > 0) {
        $table .= '<table>';

        // Print headers.
        $table .= '<tr>';

        foreach ($results->getColumnHeaders() as $header) {
            $table .= '<th>' . $header->name . '</th>';
        }
        $table .= '</tr>';

        // Print table rows.
        foreach ($results->getRows() as $row) {
            $table .= '<tr>';
            foreach ($row as $cell) {
                $table .= '<td>'
                    . htmlspecialchars($cell, ENT_NOQUOTES)
                    . '</td>';
            }
            $table .= '</tr>';
        }
        $table .= '</table>';

    } else {
        $table .= '<p>No Results Found.</p>';
    }
    print $table;
}

function printColumnHeaders(&$results) {
    $html = '';
    $headers = $results->getColumnHeaders();

    foreach ($headers as $header) {
        $html .= <<<HTML
<pre>
Column Name       = {$header->getName()}
Column Type       = {$header->getColumnType()}
Column Data Type  = {$header->getDataType()}
</pre>
HTML;
    }
    print $html;
}

function printQueryInfo(&$results) {
    $query = $results->getQuery();
    $html = <<<HTML
<pre>
Ids         = {$query->getIds()}
Metrics     = {$query->getMetrics()}
Dimensions  = {$query->getDimensions()}
Sort        = {$query->getSort()}
Filters     = {$query->getFilters()}
Max Results = {$query->getMax_results()}
</pre>
HTML;

    print $html;
}

function printProfileInfo(&$results) {
    $profileInfo = $results->getProfileInfo();

    $html = <<<HTML
<pre>
Account ID               = {$profileInfo->getAccountId()}
Web Property ID          = {$profileInfo->getWebPropertyId()}
Internal Web Property ID = {$profileInfo->getInternalWebPropertyId()}
Profile ID               = {$profileInfo->getProfileId()}
Profile Name             = {$profileInfo->getProfileName()}
Table ID                 = {$profileInfo->getTableId()}
</pre>
HTML;

    print $html;
}

function printReportInfo(&$results) {
    $html = <<<HTML
  <pre>
Kind                  = {$results->getKind()}
ID                    = {$results->getId()}
Self Link             = {$results->getSelfLink()}
Total Results         = {$results->getTotalResults()}
</pre>
HTML;

    print $html;
}

function printTotalsForAllResults(&$results) {
    $totals = $results->getTotalsForAllResults();

    foreach ($totals as $metricName => $metricTotal) {
        $html .= "Metric Name  = $metricName\n";
        $html .= "Metric Total = $metricTotal";
    }

    print $html;
}
