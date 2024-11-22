document.getElementById('data-form').addEventListener('submit', function(event) {
    event.preventDefault();
    // Handle form submission logic
    alert('Form submitted!');
});

document.querySelector('.cancel').addEventListener('click', function() {
    // Handle cancel button logic
    document.getElementById('data-form').reset();
});
