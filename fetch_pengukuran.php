<?php
// fetch_pengukuran.php

// Include your database connection file
require_once 'konfig.php'; // Adjust the path as necessary

// Check if id_noir is set in the GET request
if (isset($_GET['id_noir'])) {
    // Get the id_noir from the request and ensure it's an integer
    $id_noir = (int) $_GET['id_noir']; // Cast to integer

    // Prepare the SQL statement to fetch id_form from form table
    $query = "SELECT id_form FROM form WHERE id_noir = ?";

    // Initialize statement
    if ($stmt = $conn->prepare($query)) {
        // Bind parameter
        $stmt->bind_param("i", $id_noir); // Using "i" for integer

        // Execute statement
        if ($stmt->execute()) {
            // Get the result from query
            $result = $stmt->get_result(); // Use get_result() to fetch all results

            if ($result->num_rows > 0) {
                $results = array(); // Array to store all id_form

                // Loop through all rows
                while ($row = $result->fetch_assoc()) {
                    $id_form = $row['id_form']; // Get id_form from form table

                    // Now query the buffer table to check if status = 0
                    $bufferQuery = "SELECT id_form FROM buffer WHERE id_form = ? AND status = 0";

                    if ($bufferStmt = $conn->prepare($bufferQuery)) {
                        // Bind id_form to the buffer query
                        $bufferStmt->bind_param("i", $id_form);

                        // Execute the query
                        if ($bufferStmt->execute()) {
                            $bufferResult = $bufferStmt->get_result();

                            // If status = 0 found, add id_form to results
                            if ($bufferResult->num_rows > 0) {
                                $results[] = $id_form; // Add id_form to results
                            }
                        } else {
                            echo "Error executing buffer statement: " . $bufferStmt->error;
                        }

                        // Close the buffer statement
                        $bufferStmt->close();
                    } else {
                        echo "Error preparing buffer statement: " . $conn->error;
                    }
                }

                // Display the results (e.g., with implode if you want output as string)
                echo implode(", ", $results);
            } else {
                // If no results found in the form table
                echo "No records found.";
            }
        } else {
            echo "Error executing statement: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
} else {
    echo "id_noir parameter missing";
}

// Close the database connection
$conn->close();
