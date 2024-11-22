function confirmLogout() {
    Swal.fire({
        title: 'Konfirmasi Logout',
        text: 'Apakah Anda yakin ingin logout?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ya, logout!',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            // Lakukan tindakan logout di sini
            window.location.href = 'logout.php'; // Ganti dengan URL yang sesuai
        }
    });
}

function updateClock() {
    const clockElement = document.getElementById('clock');
    const now = new Date();
    const hours = String(now.getHours()).padStart(2, '0');
    const minutes = String(now.getMinutes()).padStart(2, '0');
    clockElement.textContent = `${hours}:${minutes}`;
}

function validateFile() {
    const fileInput = document.getElementById('upload');
    const file = fileInput.files[0];
    if (file) {
        if (file.size > 1024 * 1024) {
            alert("File size must be less than 1MB.");
            fileInput.value = ""; // Clear the input
        } else if (!file.name.endsWith('.jpg')) {
            alert("Only .jpg files are allowed.");
            fileInput.value = ""; // Clear the input
        }
    }
}

function addInspectionItem() {
    const table = document.getElementById('inspectionTable').getElementsByTagName('tbody')[0];
    const rowCount = table.rows.length / 2;
    const rowIndex = rowCount + 1;
    
    // New row for inspection item
    const newRow = table.insertRow(rowCount * 2);
    newRow.setAttribute('data-row-index', rowIndex);
    newRow.innerHTML = `
        <td>${rowIndex}</td>
        <td><input type="text" class="table-input"></td>
        <td><input type="text" class="table-input"></td>
        <td><input type="text" class="table-input"></td>
        <td>
            <select class="table-select">
                <option value="">Select</option>
                <option value="dig-cal">Dig-Cal</option>
                <option value="contracer">Contracer</option>
                <option value="etc">Etc</option>
            </select>
        </td>
        <td>
            <select class="table-select">
                <option value="mm">mm</option>
                <option value="cm">cm</option>
                <option value="m">m</option>
            </select>
        </td>
        <td>
            <div class="file-container"></div>
            <input type="file" class="file-upload" data-row-index="${rowIndex}" onchange="handleDrawingUpload(this)">
        </td>
        <td><button onclick="finalizeRow(this)">Submit</button></td>
    `;

    const optionsRow = table.insertRow(rowCount * 2 + 1);
    optionsRow.innerHTML = `
        <td colspan="8" class="inspection-options">
            <div class="measurement">
                <label><input type="radio" name="measurement${rowIndex}"> TERUKUR</label>
                <div class="standar">
                    <label>STANDAR</label>
                    <button>+</button>
                    <button>-</button>
                </div>
                <label><input type="radio" name="measurement${rowIndex}"> TIDAK TERUKUR</label>
            </div>
        </td>
    `;
}

function handleDrawingUpload(input) {
    const file = input.files[0];
    const rowIndex = input.getAttribute('data-row-index');
    const fileContainer = document.querySelector(`tr[data-row-index="${rowIndex}"] .file-container`);

    if (file) {
        if (file.size > 1024 * 1024) {
            alert("File size must be less than 1MB.");
            input.value = ""; // Clear the input
        } else if (!file.name.endsWith('.jpg')) {
            alert("Only .jpg files are allowed.");
            input.value = ""; // Clear the input
        } else {
            // Create a span element to hold the file name
            const fileNameSpan = document.createElement('span');
            fileNameSpan.textContent = file.name; // Set the file name in the span
            fileNameSpan.classList.add('file-name'); // Optionally, add a class for styling
            fileContainer.innerHTML = ''; // Clear previous content
            fileContainer.appendChild(fileNameSpan); // Append the span to display the file name

            // Add a hidden input to store the file name
            const hiddenInput = document.createElement('input');
            hiddenInput.type = 'hidden';
            hiddenInput.name = `file-${rowIndex}`; // Unique name for each file input
            hiddenInput.value = file.name;
            fileContainer.appendChild(hiddenInput); // Append the hidden input
        }
    }
}



function finalizeRow(button) {
    const row = button.parentElement.parentElement;
    const inputs = row.querySelectorAll('.table-input');
    const selects = row.querySelectorAll('.table-select');
    const submitButton = button;

    inputs.forEach(input => {
        const span = document.createElement('span');
        span.textContent = input.value;
        input.parentElement.replaceChild(span, input);
    });

    selects.forEach(select => {
        const span = document.createElement('span');
        span.textContent = select.options[select.selectedIndex].text;
        select.parentElement.replaceChild(span, select);
    });

    const fileUpload = row.querySelector('.file-upload');
    const fileNameSpan = row.querySelector('.file-name');
    fileNameSpan.textContent = fileUpload.files[0] ? fileUpload.files[0].name : '';

    submitButton.parentElement.innerHTML = `
        <button class="status-btn" onclick="editRow(this)">Edit</button>
        <button class="status-btn" onclick="deleteRow(this)">Delete</button>
    `;
}

function editRow(button) {
    const row = button.parentElement.parentElement;
    const spans = row.querySelectorAll('span');

    spans.forEach(span => {
        const input = document.createElement('input');
        input.type = 'text';
        input.value = span.textContent;
        input.className = 'table-input';
        span.parentElement.replaceChild(input, span);
    });

    const statusButtons = row.querySelectorAll('.status-btn');
    statusButtons.forEach(btn => btn.remove());

    row.lastElementChild.innerHTML = '<button onclick="finalizeRow(this)">Submit</button>';
}

function deleteRow(button) {
    const row = button.parentElement.parentElement;
    row.remove();
}



updateClock();
setInterval(updateClock, 1000);
