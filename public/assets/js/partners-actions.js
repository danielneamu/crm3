// ===== GLOBAL VARIABLES =====
let availableTags = [];
let selectedTags = [];
let contactCounter = 0;

// ===== DOCUMENT READY =====
$(document).ready(function () {
    loadAvailableTags();

    // Add Partner
    $('#btnAddPartner').click(function () {
        $('#partnerModalLabel').text('Add Partner');
        $('#partnerForm')[0].reset();
        $('#partnerId').val('');
        selectedTags = [];
        updateTagsDisplay();
        $('#contactsBody').empty();
        // ← Don't call addContactRow() here
        $('#partnerModal').modal('show');
    });


    // Edit Partner
    $('#btnEditPartner').click(function () {
        const selectedData = partnersTable.rows({ selected: true }).data()[0];
        if (!selectedData) return;

        $('#partnerModalLabel').text('Edit Partner');
        loadPartnerData(selectedData.id_parteneri);
        $('#partnerModal').modal('show');
    });

    // Save Partner
    $('#btnSavePartner').click(function () {
        savePartner();
    });

    // Add Contact Row
    $('#btnAddContact').click(function () {
        addContactRow();
    });

    // Tag Selection
    $('#tagSelect').change(function () {
        const tagId = parseInt(this.value);
        if (tagId && !selectedTags.includes(tagId)) {
            selectedTags.push(tagId);
            updateTagsDisplay();
        }
        $(this).val('');
    });

    // Manage Tags Button
    $('#btnManageTags').click(function () {
        loadTagsList();
        $('#tagModal').modal('show');
    });

    // Save Tag
    $('#tagForm').submit(function (e) {
        e.preventDefault();
        saveTag();
    });
});

// ===== TAGS MANAGEMENT =====
function loadAvailableTags() {
    $.get('../api/partners.php?action=tags', function (tags) {
        availableTags = tags;
        $('#tagSelect').empty().append('<option value="">+ Add tag</option>');
        tags.forEach(tag => {
            $('#tagSelect').append(`<option value="${tag.id}">${tag.tag}</option>`);
        });
    });
}

function loadTagsList() {
    $.get('../api/partners.php?action=tags', function (tags) {
        $('#tagsListBody').empty();
        tags.forEach(tag => {
            $('#tagsListBody').append(`
                <tr>
                    <td>${tag.tag}</td>
                    <td>${tag.comment || '-'}</td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary btn-edit-tag" data-id="${tag.id}" data-tag="${tag.tag}" data-comment="${tag.comment || ''}">
                            <i class="bi bi-pencil"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-danger btn-delete-tag" data-id="${tag.id}">
                            <i class="bi bi-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
        });
    });
}

$(document).on('click', '.btn-edit-tag', function () {
    $('#tagId').val($(this).data('id'));
    $('#tagName').val($(this).data('tag'));
    $('#tagComment').val($(this).data('comment'));
});

$(document).on('click', '.btn-delete-tag', function () {
    const tagId = $(this).data('id');
    if (confirm('Delete this tag?')) {
        $.ajax({
            url: `../api/partners.php?action=deleteTag&id=${tagId}`,
            method: 'DELETE',
            success: function (response) {
                if (response.success) {
                    showToast('Success','Tag succesfully deleted', 'success');
                    loadTagsList();
                    loadAvailableTags();
                } else {
                    showToast('Error', 'Error: ' + response.error, 'error');
                }
            }
        });
    }
});

function saveTag() {
    const data = {
        id: $('#tagId').val(),
        tag: $('#tagName').val(),
        comment: $('#tagComment').val()
    };

    $.ajax({
        url: '../api/partners.php?action=saveTag',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function (response) {
            if (response.success) {
                showToast('Success','Tag saved', 'success');
                $('#tagForm')[0].reset();
                $('#tagId').val('');
                loadTagsList();
                loadAvailableTags();
            } else {
                showToast('Error', 'Error: ' + response.error, 'error');
            }
        }
    });
}

function updateTagsDisplay() {
    $('#tagsContainer').empty();
    selectedTags.forEach(tagId => {
        const tag = availableTags.find(t => t.id == tagId);
        if (tag) {
            $('#tagsContainer').append(`
                <span class="badge bg-info me-1 mb-1">
                    ${tag.tag}
                    <i class="bi bi-x-circle ms-1" style="cursor:pointer" onclick="removeTag(${tagId})"></i>
                </span>
            `);
        }
    });
}

function removeTag(tagId) {
    selectedTags = selectedTags.filter(id => id !== tagId);
    updateTagsDisplay();
}

// ===== CONTACTS MANAGEMENT =====
function addContactRow(contact = null) {
    const id = contact ? contact.id : '';
    const name = contact ? contact.name : '';
    const role = contact ? contact.role : '';
    const phone = contact ? contact.phone : '';
    const email = contact ? contact.email : '';
    const comments = contact ? contact.comments : '';

    const row = `
        <tr data-contact-id="${id}">
            <td><input type="text" class="form-control form-control-sm contact-name" value="${name}" ></td>
            <td><input type="text" class="form-control form-control-sm contact-role" value="${role}"></td>
            <td><input type="text" class="form-control form-control-sm contact-phone" value="${phone}"></td>
            <td><input type="email" class="form-control form-control-sm contact-email" value="${email}" ></td>
            <td><input type="text" class="form-control form-control-sm contact-comments" value="${comments}"></td>
            <td>
                <button type="button" class="btn btn-sm btn-danger btn-remove-contact">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        </tr>
    `;
    $('#contactsBody').append(row);
}

$(document).on('click', '.btn-remove-contact', function () {
    $(this).closest('tr').remove();
});

// ===== PARTNER CRUD =====
function loadPartnerData(partnerId) {
    $.get(`../api/partners.php?action=get&id=${partnerId}`, function (partner) {
        $('#partnerId').val(partner.id_parteneri);
        $('#partnerName').val(partner.name_parteneri);
        $('#partnerType').val(partner.type_parteneri);

        selectedTags = partner.tags.map(t => t.id);
        updateTagsDisplay();

        $('#contactsBody').empty();
        if (partner.contacts && partner.contacts.length > 0) {
            partner.contacts.forEach(contact => addContactRow(contact));
        } else {
            addContactRow();
        }
    });
}

function savePartner() {
    if (!$('#partnerForm')[0].checkValidity()) {
        $('#partnerForm')[0].reportValidity();
        return;
    }

    const contacts = [];
    $('#contactsBody tr').each(function () {
        const name = $(this).find('.contact-name').val().trim();
        const email = $(this).find('.contact-email').val().trim();

        // Only include if name OR email is filled
        if (name || email) {
            contacts.push({
                id: $(this).data('contact-id') || '',
                name: name,
                role: $(this).find('.contact-role').val(),
                phone: $(this).find('.contact-phone').val(),
                email: email,
                comments: $(this).find('.contact-comments').val()
            });
        }
    });

    const data = {
        id_parteneri: $('#partnerId').val(),
        name_parteneri: $('#partnerName').val(),
        type_parteneri: $('#partnerType').val(),
        tags: selectedTags,
        contacts: contacts
    };

    $.ajax({
        url: '../api/partners.php?action=save',
        method: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(data),
        success: function (response) {
            if (response.success) {
                $('#partnerModal').modal('hide');
                partnersTable.ajax.reload();
                showToast('Success','Partner saved successfully!', 'success');
            } else {
                showToast('Error','Error: ' + (response.error || 'Unknown error'), 'error');
            }
        },
        error: function () {
            showToast('Error', 'Failed to save partner', 'error');
        }
    });
}
