<?php
// Fetch the 5 most recent login attempts for the currently logged-in user

// Perform a MySQL query to select records from 'log_login' table for the current user
// Ordered by 'id' descending (most recent first), limited to 5 records
$RecentLogins = mysql_query(
    "SELECT * FROM `log_login` 
     WHERE userid='{$UserAuthData['userid']}' 
     ORDER BY id DESC 
     LIMIT 5"
);

// Loop through each fetched record
while($row = mysql_fetch_array($RecentLogins)) {

    // Convert the timestamp from the database to a UNIX timestamp
    $date = strtotime($row['timestamp']);

    // Format the date in a human-readable form (e.g., January 1, 2025, 3:45 PM)
    $date = date("F j, Y, g:i A", $date);

    // Start a new table row for this login record
    echo '<tr>';

        // Display the IP address from which the user logged in
        echo '<td>'.$row['ip'].'</td>';

        // Display the operating system used during the login
        echo '<td>'.$row['os'].'</td>';

        // Display the browser used during the login
        echo '<td>'.$row['browser'].'</td>';

        // Display the formatted timestamp of the login
        echo '<td>'.$date.'</td>';

    // End the table row
    echo '</tr>';
}
?>
