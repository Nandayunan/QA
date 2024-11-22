document.addEventListener("DOMContentLoaded", function() {
    function enableInput(rowNumber) {
        const row = document.querySelector(`tr[data-row="${rowNumber}"]`);
        const inputs = row.querySelectorAll(".input-text");
        const select = row.querySelector(".input-select");
        const submitButton = row.querySelector(".submit-button");
        const inputButton = row.querySelector(".input-button");

        inputs.forEach(input => input.disabled = false);
        select.disabled = false;
        inputButton.disabled = true;
        inputButton.style.cursor = "not-allowed";
        inputButton.style.backgroundColor = "navy";
    }

    function confirmSubmit(rowNumber) {
        Swal.fire({
            title: 'Confirm Submission',
            text: 'Are you sure you want to submit?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, submit it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                const row = document.querySelector(`tr[data-row="${rowNumber}"]`);
                const inputs = row.querySelectorAll(".input-text");
                const select = row.querySelector(".input-select");

                // Disable the inputs and select after submission
                inputs.forEach(input => input.disabled = true);
                select.disabled = true;

                // Change the submit button to indicate the row is locked
                const submitButton = row.querySelector(".submit-button");
                submitButton.disabled = true;
                submitButton.style.cursor = "not-allowed";
                submitButton.style.backgroundColor = "navy";
            }
        });
    }

    function finalSubmit() {
        Swal.fire({
            title: 'Confirm Final Submission',
            text: 'Are you sure you want to submit all entries?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, submit all!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Handle final submission logic here
                Swal.fire('Submitted!', 'All entries have been submitted.', 'success');
            }
        });
    }

    function confirmLogout() {
        Swal.fire({
            title: 'Confirm Logout',
            text: 'Are you sure you want to logout?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Yes, logout',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Handle logout logic here
                
            window.location.href = "logout.php"; // Redirect to logout.php
                
                Swal.fire('Logged out!', 'You have been logged out.', 'success');
            }
        });
    }

    window.enableInput = enableInput;
    window.confirmSubmit = confirmSubmit;
    window.finalSubmit = finalSubmit;
    window.confirmLogout = confirmLogout;
});
