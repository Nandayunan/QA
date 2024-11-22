// edit.js

document.getElementById("toggleFormButton").addEventListener("click", function() {
    Swal.fire({
        title: 'Tambah Baris Baru',
        html:
        '<input id="newNoPpb" class="swal2-input" placeholder="NO. PPB">' +
        '<input id="newPartPengerjaan" class="swal2-input" placeholder="PART PENGERJAAN">' +
        '<input id="newJenisPengecekan" class="swal2-input" placeholder="JENIS PENGECEKAN">' +
        '<input id="newReqFrom" class="swal2-input" placeholder="REQ FROM">' +
        '<input id="newPIC" class="swal2-input" placeholder="PIC">' +
        '<input id="newReceive" type="date" class="swal2-input">' +
        '<select id="newStatus" class="swal2-input">' +
        '   <option value="1">Waiting</option>' +
        '   <option value="2">Progress</option>' +
        '   <option value="3">Finish</option>' +
        '</select>' +
        '<input id="newFile" class="swal2-input" placeholder="FILE">' +
        '<input id="newMulai" type="date" class="swal2-input">' +
        '<input id="newEstSelesai" type="date" class="swal2-input">',
        focusConfirm: false,
        preConfirm: () => {
            return {
                no_ppb: document.getElementById('newNoPpb').value,
                partPengerjaan: document.getElementById('newPartPengerjaan').value,
                jenisPengecekan: document.getElementById('newJenisPengecekan').value,
                reqFrom: document.getElementById('newReqFrom').value,
                pic: document.getElementById('newPIC').value,
                receive: document.getElementById('newReceive').value,
                status: document.getElementById('newStatus').value,
                file: document.getElementById('newFile').value,
                mulai: document.getElementById('newMulai').value,
                estSelesai: document.getElementById('newEstSelesai').value
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Send new data to server (AJAX example)
            const formData = new FormData();
            formData.append('no_ppb', result.value.no_ppb);
            formData.append('partPengerjaan', result.value.partPengerjaan);
            formData.append('jenisPengecekan', result.value.jenisPengecekan);
            formData.append('reqFrom', result.value.reqFrom);
            formData.append('pic', result.value.pic);
            formData.append('receive', result.value.receive);
            formData.append('status', result.value.status);
            formData.append('file', result.value.file);
            formData.append('mulai', result.value.mulai);
            formData.append('estSelesai', result.value.estSelesai);

            fetch('add_row.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Handle response from server
                if (data.success) {
                    // Update UI or handle success message
                    Swal.fire('Data berhasil ditambahkan!', '', 'success');
                    // Refresh table or update specific row if necessary
                    // Example: Reload table
                    location.reload();
                } else {
                    Swal.fire('Error', 'Gagal menambahkan data', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Gagal menambahkan data', 'error');
            });
        }
    });
});

function editRow(no_ppb) {
    // Fetch existing data or retrieve via AJAX if necessary
    // Example: Fetch existing data and pre-fill inputs in SweetAlert2
    Swal.fire({
        title: 'Edit Data',
        html:
        '<input id="editPartPengerjaan" class="swal2-input" placeholder="Part Pengerjaan">' +
        '<input id="editJenisPengecekan" class="swal2-input" placeholder="Jenis Pengecekan">' +
        '<input id="editReqFrom" class="swal2-input" placeholder="REQ FROM">' +
        '<input id="editPIC" class="swal2-input" placeholder="PIC">' +
        '<input id="editReceive" type="date" class="swal2-input">' +
        '<select id="editStatus" class="swal2-input">' +
        '   <option value="1">Waiting</option>' +
        '   <option value="2">Progress</option>' +
        '   <option value="3">Finish</option>' +
        '</select>' +
        '<input id="editMulai" type="date" class="swal2-input">' +
        '<input id="editEstSelesai" type="date" class="swal2-input">',
        focusConfirm: false,
        preConfirm: () => {
            return {
                no_ppb: no_ppb,
                partPengerjaan: document.getElementById('editPartPengerjaan').value,
                jenisPengecekan: document.getElementById('editJenisPengecekan').value,
                reqFrom: document.getElementById('editReqFrom').value,
                pic: document.getElementById('editPIC').value,
                receive: document.getElementById('editReceive').value,
                status: document.getElementById('editStatus').value,
                mulai: document.getElementById('editMulai').value,
                estSelesai: document.getElementById('editEstSelesai').value
            };
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Send updated data to server (AJAX example)
            const formData = new FormData();
            formData.append('no_ppb', result.value.no_ppb);
            formData.append('partPengerjaan', result.value.partPengerjaan);
            formData.append('jenisPengecekan', result.value.jenisPengecekan);
            formData.append('reqFrom', result.value.reqFrom);
            formData.append('pic', result.value.pic);
            formData.append('receive', result.value.receive);
            formData.append('status', result.value.status);
            formData.append('mulai', result.value.mulai);
            formData.append('estSelesai', result.value.estSelesai);

            fetch('updatestatus.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Handle response from server
                if (data.success) {
                    // Update UI or handle success message
                    Swal.fire('Data berhasil diupdate!', '', 'success');
                    // Refresh table or update specific row if necessary
                    // Example: Reload table
                    location.reload();
                } else {
                    Swal.fire('Error', 'Gagal melakukan update data', 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire('Error', 'Gagal melakukan update data', 'error');
            });
        }
    });
}
