/**
 * members.js - handles clan selection and dependent dropdowns on add/edit member forms
 * Requires: jQuery, family_tree.ajax_url, family_tree.nonce localized
 */

jQuery(document).ready(function ($) {
    // ensure console message
    console.log('Members module JS loaded');

    function populateClans(selectSelector, selectedId) {
        $.post(family_tree.ajax_url, {
            action: 'get_all_clans_simple',
            nonce: family_tree.nonce
        }, function (res) {
            if (res.success) {
                const clans = res.data;
                const sel = $(selectSelector);
                sel.empty();
                sel.append('<option value="">-- Select Clan --</option>');
                clans.forEach(c => {
                    sel.append(`<option value="${c.id}" ${selectedId && selectedId == c.id ? 'selected' : ''}>${c.clan_name}</option>`);
                });
            } else {
                console.error('Failed loading clans', res);
            }
        });
    }

    // When clan changes, load locations & surnames
    $(document).on('change', '#clan_id', function () {
        const clanId = $(this).val();
        if (!clanId) {
            $('#clan_location_id').empty().append('<option value="">-- Select Location --</option>');
            $('#clan_surname_id').empty().append('<option value="">-- Select Surname --</option>');
            return;
        }

        $.post(family_tree.ajax_url, {
            action: 'get_clan_details',
            nonce: family_tree.nonce,
            clan_id: clanId
        }, function (res) {
            if (res.success) {
                const data = res.data;
                const locSel = $('#clan_location_id');
                const snSel = $('#clan_surname_id');
                locSel.empty().append('<option value="">-- Select Location --</option>');
                snSel.empty().append('<option value="">-- Select Surname --</option>');

                if (data.locations && data.locations.length) {
                    data.locations.forEach(l => {
                        locSel.append(`<option value="${l.id}">${l.location_name}</option>`);
                    });
                }
                if (data.surnames && data.surnames.length) {
                    data.surnames.forEach(s => {
                        snSel.append(`<option value="${s.id}">${s.last_name}</option>`);
                    });
                }
            } else {
                console.error('get_clan_details failed', res);
            }
        });
    });

    // Initialize clan select on add/edit forms if present
    if ($('#clan_id').length) {
        populateClans('#clan_id', $('#clan_id').data('selected'));
    }

    // On edit page we may have preselected clan, so trigger change to load dependencies
    const preClan = $('#clan_id').data('selected');
    const preLocation = $('#clan_location_id').data('selected');
    const preSurname = $('#clan_surname_id').data('selected');

    if (preClan) {
        // ensure options loaded, then select pre values
        populateClans('#clan_id', preClan);
        // Wait for clans populate then trigger change and set selected values inside callback
        // We'll poll until options appear (small helper)
        const interval = setInterval(() => {
            if ($('#clan_id option').length > 1) {
                $('#clan_id').val(preClan).trigger('change');
                // after clan change loads locations/surnames, set them
                setTimeout(() => {
                    if (preLocation) $('#clan_location_id').val(preLocation);
                    if (preSurname) $('#clan_surname_id').val(preSurname);
                }, 400);
                clearInterval(interval);
            }
        }, 150);
    }

});
