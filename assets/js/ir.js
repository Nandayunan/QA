document.addEventListener('DOMContentLoaded', () => {
    const addRowButton = document.querySelector('.add-row-btn');

    addRowButton.addEventListener('click', () => {
        const newIrNo = document.getElementById('new-ir-no').value;
        const newJenisIr = document.getElementById('new-jenis-ir').value;
        const newJenisProduk = document.getElementById('new-jenis-produk').value;
        const newPart = document.getElementById('new-part').value;

        if (newIrNo && newJenisIr && newJenisProduk && newPart) {
            const table = document.getElementById('ir-table').getElementsByTagName('tbody')[0];
            const newRow = table.insertRow(table.rows.length - 1);

            const cell1 = newRow.insertCell(0);
            const cell2 = newRow.insertCell(1);
            const cell3 = newRow.insertCell(2);
            const cell4 = newRow.insertCell(3);
            const cell5 = newRow.insertCell(4);

            cell1.textContent = newIrNo;
            cell2.textContent = newJenisIr;
            cell3.textContent = newJenisProduk;
            cell4.textContent = newPart;
            cell5.innerHTML = `
                <button class="action-btn edit-btn">edit</button>
                <button class="action-btn delete-btn">hapus</button>
            `;

            // Kosongkan kolom input
            document.getElementById('new-ir-no').value = '';
            document.getElementById('new-jenis-ir').value = '';
            document.getElementById('new-jenis-produk').value = '';
            document.getElementById('new-part').value = '';

            // Re-add event listeners for new edit and delete buttons
            newRow.querySelector('.edit-btn').addEventListener('click', event => {
                console.log('Edit button clicked');
            });

            newRow.querySelector('.delete-btn').addEventListener('click', event => {
                console.log('Delete button clicked');
            });
        } else {
            alert('Semua kolom harus diisi.');
        }
    });
});

function updateClock() {
    var now = new Date();
    var hours = now.getHours();
    var minutes = now.getMinutes();
    var seconds = now.getSeconds();
    var timeString = hours.toString().padStart(2, '0') + ':' + minutes.toString().padStart(2, '0') + ':' + seconds.toString().padStart(2, '0');
    document.getElementById('clock').textContent = timeString;
}

document.getElementById('logout-button').addEventListener('click', function() {
    if (confirm("Are you sure you want to logout?")) {
        alert("Logout successful");
    }
});

document.querySelectorAll('.edit-btn').forEach(item => {
    item.addEventListener('click', event => {
        console.log('Edit button clicked');
    });
});

document.querySelectorAll('.delete-btn').forEach(item => {
    item.addEventListener('click', event => {
        console.log('Delete button clicked');
    });
});

updateClock();
setInterval(updateClock, 1000);
