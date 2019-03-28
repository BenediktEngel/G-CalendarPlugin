<?php
$apikey;
$calendarID;
$attributes = array();
$events = array();
$today = new DateTime(DateTime::createFromFormat("Y-m-d H:i:s", date('Y-m-d H:i:s'))->format(option('benediktengel.G-CalendarPlugin.formatDate')));
$i = 0;

// Get Values from config.php

// API-event & Calendar ID
if (option('benediktengel.G-CalendarPlugin.apikey')!= null && option('benediktengel.G-CalendarPlugin.calendarID')!= null) {
    $apikey = option('benediktengel.G-CalendarPlugin.apikey');
    $calendarID = option('benediktengel.G-CalendarPlugin.calendarID');
} else {
    if ((option('benediktengel.G-CalendarPlugin.apikey')== null || option('benediktengel.G-CalendarPlugin.apikey')== "")&& (option('benediktengel.G-CalendarPlugin.calendarID')== null || option('benediktengel.G-CalendarPlugin.calendarID')== "")) {
        trigger_error("Error: API-event and calendarID are missing!", E_USER_ERROR);
    } elseif (option('benediktengel.G-CalendarPlugin.apikey')== null || option('benediktengel.G-CalendarPlugin.apikey')== "") {
        trigger_error("Error: API-event is missing!", E_USER_ERROR);
    } elseif (option('benediktengel.G-CalendarPlugin.calendarID')== null || option('benediktengel.G-CalendarPlugin.calendarID')== "") {
        trigger_error("Error: CalendarID is missing!", E_USER_ERROR);
    }
}

// Attributes
if (option('benediktengel.G-CalendarPlugin.attributes') != null) {
    foreach (option('benediktengel.G-CalendarPlugin.attributes') as $attribute) {
        array_push($attributes, $attribute) ;
    }
}

// Get JSON with the events
$stream = @file_get_contents('https://www.googleapis.com/calendar/v3/calendars/'.$calendarID.'/events?singleEvents=true&orderBy=startTime&key='.$apikey);
if ($stream === false) {
    trigger_error("Error: GET Calendar doesn't work", E_USER_ERROR);
} else {
    $obj = json_decode($stream, true);
}

// No Attributes
if ($attributes == null) {
    foreach ($obj['items'] as $event) {
        $events[$i] = array();
        $events[$i] += ["title" => $event['summary']];
        $events[$i] += ["dateStart" =>  new DateTime(DateTime::createFromFormat("Y-m-d?H:i:sP", $event['start']['dateTime'])->format(option('benediktengel.G-CalendarPlugin.formatDate')))];
        $events[$i] += ["timeStart" =>  new DateTime(DateTime::createFromFormat("Y-m-d?H:i:sP", $event['start']['dateTime'])->format(option('benediktengel.G-CalendarPlugin.formatTime')))];
        $events[$i] += ["dateEnd" =>  new DateTime(DateTime::createFromFormat("Y-m-d?H:i:sP", $event['end']['dateTime'])->format(option('benediktengel.G-CalendarPlugin.formatDate')))];
        $events[$i] += ["timeEnd" =>  new DateTime(DateTime::createFromFormat("Y-m-d?H:i:sP", $event['end']['dateTime'])->format(option('benediktengel.G-CalendarPlugin.formatTime')))];
        $events[$i] += ["url" => $event['htmlLink']];
        if (isset($event['description'])) {
            $events[$i] += ["description" => $event['description']];
        }
        if (isset($event['location'])) {
            $events[$i] += ["location" => $event['location']];
        }
        $i++;
    }
}
// With Attributes
else {
    $i = 0;
    foreach ($obj['items'] as $event) {
        $events[$i] = array();
        foreach ($attributes as $attirbute) {
            if ($attirbute == 'title') {
                $events[$i] += ["title" => $event['summary']];
            }
        }
        foreach ($attributes as $attirbute) {
            if ($attirbute == 'dateStart') {
                $events[$i] += ["dateStart" =>  new DateTime(DateTime::createFromFormat("Y-m-d?H:i:sP", $event['start']['dateTime'])->format(option('benediktengel.G-CalendarPlugin.formatDate')))];
            }
        }
        foreach ($attributes as $attirbute) {
            if ($attirbute == 'timeStart') {
                $events[$i] += ["timeStart" =>  new DateTime(DateTime::createFromFormat("Y-m-d?H:i:sP", $event['start']['dateTime'])->format(option('benediktengel.G-CalendarPlugin.formatTime')))];
            }
        }
        foreach ($attributes as $attirbute) {
            if ($attirbute == 'dateEnd') {
                $events[$i] += ["dateEnd" =>  new DateTime(DateTime::createFromFormat("Y-m-d?H:i:sP", $event['end']['dateTime'])->format(option('benediktengel.G-CalendarPlugin.formatDate')))];
            }
        }
        foreach ($attributes as $attirbute) {
            if ($attirbute == 'timeEnd') {
                $events[$i] += ["timeEnd" =>  new DateTime(DateTime::createFromFormat("Y-m-d?H:i:sP", $event['end']['dateTime'])->format(option('benediktengel.G-CalendarPlugin.formatTime')))];
            }
        }
        foreach ($attributes as $attirbute) {
            if ($attirbute == 'description') {
                if (isset($event['description'])) {
                    $events[$i] += ["description" => $event['description']];
                }
            }
        }
        foreach ($attributes as $attirbute) {
            if ($attirbute == 'location') {
                if (isset($event['location'])) {
                    $events[$i] += ["location" => $event['location']];
                }
            }
        }
        foreach ($attributes as $attirbute) {
            if ($attirbute == 'url') {
                $events[$i] += ["url" => $event['htmlLink']];
            }
        }
        $i++;
    }
}


// Print the calendar only upcoming
if (sizeof($events) != 0) {
    if (option('benediktengel.G-CalendarPlugin.upcoming') == true && isset($events[0]['dateEnd'])) {
        for ($s=0; $s < sizeof($events); $s++) {
            if ($today <= $events[$s]['dateEnd']) {
                echo "<div class='calendar-event'>" ;
                if (isset($events[$s]['title'])) {
                    echo "<h4 class='calendar-title'>".$events[$s]['title']."</h4>";
                }
                // Start TZ und Ende TZ
                if (isset($events[$s]['dateStart']) && isset($events[$s]['timeStart']) && isset($events[$s]['dateEnd']) && isset($events[$s]['timeEnd'])) {
                    echo "<p class='calendar-datetime'><span class='calendar-start'>";
                    echo $events[$s]['dateStart']->format(option('benediktengel.G-CalendarPlugin.formatDate'));
                    echo " ".$events[$s]['timeStart']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                    echo "</span> - <span class='calendar-end'>";
                    echo $events[$s]['dateEnd']->format(option('benediktengel.G-CalendarPlugin.formatDate'))." ".$events[$s]['timeEnd']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                    echo "</span></p>";
                }
                // Start TZ und Ende T
                elseif (isset($events[$s]['dateStart']) && isset($events[$s]['timeStart']) && isset($events[$s]['dateEnd'])) {
                    echo "<p class='calendar-datetime'><span class='calendar-start'>";
                    echo $events[$s]['dateStart']->format(option('benediktengel.G-CalendarPlugin.formatDate'));
                    echo " ".$events[$s]['timeStart']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                    echo "</span> - <span class='calendar-end'>";
                    echo $events[$s]['dateEnd']->format(option('benediktengel.G-CalendarPlugin.formatDate'));
                    echo "</span></p>";
                }
                // Start TZ Und Ende Z
                elseif (isset($events[$s]['dateStart']) && isset($events[$s]['timeStart']) && isset($events[$s]['timeEnd'])) {
                    echo "<p class='calendar-datetime'><span class='calendar-start'>";
                    echo $events[$s]['dateStart']->format(option('benediktengel.G-CalendarPlugin.formatDate'));
                    echo " ".$events[$s]['timeStart']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                    echo "</span> - <span class='calendar-end'>";
                    echo $events[$s]['timeEnd']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                    echo "</span></p>";
                }
                // Start T und Ende TZ
                elseif (isset($events[$s]['dateStart']) && isset($events[$s]['dateEnd']) && isset($events[$s]['timeEnd'])) {
                    echo "<p class='calendar-datetime'><span class='calendar-start'>";
                    echo $events[$s]['dateStart']->format(option('benediktengel.G-CalendarPlugin.formatDate'));
                    echo "</span> - <span class='calendar-end'>";
                    echo $events[$s]['dateEnd']->format(option('benediktengel.G-CalendarPlugin.formatDate'))." ".$events[$s]['timeEnd']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                    echo "</span></p>";
                }
                // Start Z und Ende TZ
                elseif (isset($events[$s]['timeStart']) && isset($events[$s]['dateEnd']) && isset($events[$s]['timeEnd'])) {
                    echo "<p class='calendar-datetime'><span class='calendar-start'>";
                    echo $events[$s]['timeStart']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                    echo "</span> - <span class='calendar-end'>";
                    echo $events[$s]['dateEnd']->format(option('benediktengel.G-CalendarPlugin.formatDate'))." ".$events[$s]['timeEnd']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                    echo "</span></p>";
                }
                // Start t und ende t
                elseif (isset($events[$s]['dateStart']) && isset($events[$s]['dateEnd'])) {
                    echo "<p class='calendar-datetime'><span class='calendar-start'>";
                    echo $events[$s]['dateStart']->format(option('benediktengel.G-CalendarPlugin.formatDate'));
                    echo "</span> - <span class='calendar-end'>";
                    echo $events[$s]['dateEnd']->format(option('benediktengel.G-CalendarPlugin.formatDate'));
                    echo "</span></p>";
                }
                // Start z und ende z
                elseif (isset($events[$s]['timeStart']) && isset($events[$s]['timeEnd'])) {
                    echo "<p class='calendar-datetime'><span class='calendar-start'>";
                    echo " ".$events[$s]['timeStart']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                    echo "</span> - <span class='calendar-end'>";
                    echo $events[$s]['timeEnd']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                    echo "</span></p>";
                }
                //start tz
                elseif (isset($events[$s]['dateStart']) && isset($events[$s]['timeStart'])) {
                    echo "<p class='calendar-datetime'><span class='calendar-start'>";
                    echo $events[$s]['dateStart']->format(option('benediktengel.G-CalendarPlugin.formatDate'));
                    echo " ".$events[$s]['timeStart']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                    echo "</span></p>";
                }
                // Start t
                elseif (isset($events[$s]['dateStart'])) {
                    echo "<p class='calendar-datetime'><span class='calendar-start'>";
                    echo $events[$s]['dateStart']->format(option('benediktengel.G-CalendarPlugin.formatDate'))."</span></p>";
                }
                //Start z
                elseif (isset($events[$s]['timeStart'])) {
                    echo "<p class='calendar-datetime'><span class='calendar-start'>";
                    echo $events[$s]['timeStart']->format(option('benediktengel.G-CalendarPlugin.formatTime'))."</span></p>";
                }
                //Ende zt
                elseif (isset($events[$s]['dateEnd']) && isset($events[$s]['timeEnd'])) {
                    echo "<p class='calendar-datetime'><span class='calendar-end'>";
                    echo $events[$s]['dateEnd']->format(option('benediktengel.G-CalendarPlugin.formatDate'))." ".$events[$s]['timeEnd']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                    echo "</span></p>";
                }
                // ende t
                elseif (isset($events[$s]['dateEnd'])) {
                    echo "<p class='calendar-datetime'><span class='calendar-end'>";
                    echo $events[$s]['dateEnd']->format(option('benediktengel.G-CalendarPlugin.formatDate'));
                    echo "</span></p>";
                }
                // ende z
                elseif (isset($events[$s]['timeEnd'])) {
                    echo "<p class='calendar-datetime'><span class='calendar-end'>";
                    echo $events[$s]['timeEnd']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                    echo "</span></p>";
                }
                if (isset($events[$s]['location'])) {
                    echo "<p class='calendar-location'>".$events[$s]['location']."</p>";
                }
                if (isset($events[$s]['description'])) {
                    if (option('benediktengel.G-CalendarPlugin.cutDescription') == true) {
                        echo "<p class='calendar-description'>".substr($events[$s]['description'], 0, option('benediktengel.G-CalendarPlugin.descriptionLength'))."... <a target='_blank' class='calendar-link' href='".$events[$s]['url']."'>".option('benediktengel.G-CalendarPlugin.linkName')."</a></p>";
                    } else {
                        echo "<p class='calendar-description'>".$events[$s]['description']."<a target='_blank' class='calendar-link' href='".$events[$s]['url']."'>".option('benediktengel.G-CalendarPlugin.linkName')."</a></p>";
                    }
                } elseif (isset($events[$s]['url'])) {
                    echo "<a target='_blank' class='calendar-link' href='".$events[$s]['url']."'>".option('benediktengel.G-CalendarPlugin.linkName')."</a>";
                }
                echo "</div>";
            }
        }
    }
    // Print the calendar all events
    else {
        for ($s=0; $s < sizeof($events); $s++) {
            echo "<div class='calendar-event'>" ;
            if (isset($events[$s]['title'])) {
                echo "<h4 class='calendar-title'>".$events[$s]['title']."</h4>";
            }
            // Start TZ und Ende TZ
            if (isset($events[$s]['dateStart']) && isset($events[$s]['timeStart']) && isset($events[$s]['dateEnd']) && isset($events[$s]['timeEnd'])) {
                echo "<p class='calendar-datetime'><span class='calendar-start'>";
                echo $events[$s]['dateStart']->format(option('benediktengel.G-CalendarPlugin.formatDate'));
                echo " ".$events[$s]['timeStart']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                echo "</span> - <span class='calendar-end'>";
                echo $events[$s]['dateEnd']->format(option('benediktengel.G-CalendarPlugin.formatDate'))." ".$events[$s]['timeEnd']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                echo "</span></p>";
            }
            // Start TZ und Ende T
            elseif (isset($events[$s]['dateStart']) && isset($events[$s]['timeStart']) && isset($events[$s]['dateEnd'])) {
                echo "<p class='calendar-datetime'><span class='calendar-start'>";
                echo $events[$s]['dateStart']->format(option('benediktengel.G-CalendarPlugin.formatDate'));
                echo " ".$events[$s]['timeStart']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                echo "</span> - <span class='calendar-end'>";
                echo $events[$s]['dateEnd']->format(option('benediktengel.G-CalendarPlugin.formatDate'));
                echo "</span></p>";
            }
            // Start TZ Und Ende Z
            elseif (isset($events[$s]['dateStart']) && isset($events[$s]['timeStart']) && isset($events[$s]['timeEnd'])) {
                echo "<p class='calendar-datetime'><span class='calendar-start'>";
                echo $events[$s]['dateStart']->format(option('benediktengel.G-CalendarPlugin.formatDate'));
                echo " ".$events[$s]['timeStart']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                echo "</span> - <span class='calendar-end'>";
                echo $events[$s]['timeEnd']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                echo "</span></p>";
            }
            // Start T und Ende TZ
            elseif (isset($events[$s]['dateStart']) && isset($events[$s]['dateEnd']) && isset($events[$s]['timeEnd'])) {
                echo "<p class='calendar-datetime'><span class='calendar-start'>";
                echo $events[$s]['dateStart']->format(option('benediktengel.G-CalendarPlugin.formatDate'));
                echo "</span> - <span class='calendar-end'>";
                echo $events[$s]['dateEnd']->format(option('benediktengel.G-CalendarPlugin.formatDate'))." ".$events[$s]['timeEnd']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                echo "</span></p>";
            }
            // Start Z und Ende TZ
            elseif (isset($events[$s]['timeStart']) && isset($events[$s]['dateEnd']) && isset($events[$s]['timeEnd'])) {
                echo "<p class='calendar-datetime'><span class='calendar-start'>";
                echo $events[$s]['timeStart']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                echo "</span> - <span class='calendar-end'>";
                echo $events[$s]['dateEnd']->format(option('benediktengel.G-CalendarPlugin.formatDate'))." ".$events[$s]['timeEnd']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                echo "</span></p>";
            }
            // Start t und ende t
            elseif (isset($events[$s]['dateStart']) && isset($events[$s]['dateEnd'])) {
                echo "<p class='calendar-datetime'><span class='calendar-start'>";
                echo $events[$s]['dateStart']->format(option('benediktengel.G-CalendarPlugin.formatDate'));
                echo "</span> - <span class='calendar-end'>";
                echo $events[$s]['dateEnd']->format(option('benediktengel.G-CalendarPlugin.formatDate'));
                echo "</span></p>";
            }
            // Start z und ende z
            elseif (isset($events[$s]['timeStart']) && isset($events[$s]['timeEnd'])) {
                echo "<p class='calendar-datetime'><span class='calendar-start'>";
                echo " ".$events[$s]['timeStart']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                echo "</span> - <span class='calendar-end'>";
                echo $events[$s]['timeEnd']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                echo "</span></p>";
            }
            //start tz
            elseif (isset($events[$s]['dateStart']) && isset($events[$s]['timeStart'])) {
                echo "<p class='calendar-datetime'><span class='calendar-start'>";
                echo $events[$s]['dateStart']->format(option('benediktengel.G-CalendarPlugin.formatDate'));
                echo " ".$events[$s]['timeStart']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                echo "</span></p>";
            }
            // Start t
            elseif (isset($events[$s]['dateStart'])) {
                echo "<p class='calendar-datetime'><span class='calendar-start'>";
                echo $events[$s]['dateStart']->format(option('benediktengel.G-CalendarPlugin.formatDate'))."</span></p>";
            }
            //Start z
            elseif (isset($events[$s]['timeStart'])) {
                echo "<p class='calendar-datetime'><span class='calendar-start'>";
                echo $events[$s]['timeStart']->format(option('benediktengel.G-CalendarPlugin.formatTime'))."</span></p>";
            }
            //Ende zt
            elseif (isset($events[$s]['dateEnd']) && isset($events[$s]['timeEnd'])) {
                echo "<p class='calendar-datetime'><span class='calendar-end'>";
                echo $events[$s]['dateEnd']->format(option('benediktengel.G-CalendarPlugin.formatDate'))." ".$events[$s]['timeEnd']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                echo "</span></p>";
            }
            // ende t
            elseif (isset($events[$s]['dateEnd'])) {
                echo "<p class='calendar-datetime'><span class='calendar-end'>";
                echo $events[$s]['dateEnd']->format(option('benediktengel.G-CalendarPlugin.formatDate'));
                echo "</span></p>";
            }
            // ende z
            elseif (isset($events[$s]['timeEnd'])) {
                echo "<p class='calendar-datetime'><span class='calendar-end'>";
                echo $events[$s]['timeEnd']->format(option('benediktengel.G-CalendarPlugin.formatTime'));
                echo "</span></p>";
            }
            if (isset($events[$s]['location'])) {
                echo "<p class='calendar-location'>".$events[$s]['location']."</p>";
            }
            if (isset($events[$s]['description'])) {
                if (option('benediktengel.G-CalendarPlugin.cutDescription') == true) {
                    echo "<p class='calendar-description'>".substr($events[$s]['description'], 0, option('benediktengel.G-CalendarPlugin.descriptionLength'))."... <a target='_blank' class='calendar-link' href='".$events[$s]['url']."'>".option('benediktengel.G-CalendarPlugin.linkName')."</a></p>";
                } else {
                    echo "<p class='calendar-description'>".$events[$s]['description']."<a target='_blank' class='calendar-link' href='".$events[$s]['url']."'>".option('benediktengel.G-CalendarPlugin.linkName')."</a></p>";
                }
            } elseif (isset($events[$s]['url'])) {
                echo "<a target='_blank' class='calendar-link' href='".$events[$s]['url']."'>".option('benediktengel.G-CalendarPlugin.linkName')."</a>";
            }
            echo "</div>";
        }
    }
} else {
    echo "No Events.";
}
