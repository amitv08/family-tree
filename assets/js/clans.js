/**
 * Family Tree Plugin - Clan Management
 * Handles tag-based input for locations and surnames,
 * and AJAX CRUD operations for clans.
 */

jQuery(document).ready(function ($) {

    console.log("Family Tree Clan module initialized");

    // ========== Tag Input Logic ==========
    const ClanTags = {
        locations: [],
        surnames: [],

        addTag(inputSelector, targetArray, containerSelector) {
            const input = $(inputSelector);
            const container = $(containerSelector);

            input.on("keydown", function (e) {
                if (e.key === "Enter" || e.key === ",") {
                    e.preventDefault();
                    const value = input.val().trim();
                    if (value && !targetArray.includes(value)) {
                        targetArray.push(value);
                        ClanTags.renderTags(targetArray, container);
                        input.val("");
                    }
                }
            });
        },

        renderTags(tags, container) {
            container.empty();
            tags.forEach((tag, index) => {
                container.append(
                    `<span class="tag">${tag}<button type="button" class="remove-tag" data-index="${index}">×</button></span>`
                );
            });
        },

        bindRemoveHandler(targetArray, containerSelector) {
            $(document).on("click", `${containerSelector} .remove-tag`, function () {
                const idx = $(this).data("index");
                targetArray.splice(idx, 1);
                ClanTags.renderTags(targetArray, $(containerSelector));
            });
        }
    };

    // Initialize tag inputs if present
    if ($("#clanLocations").length) {
        ClanTags.addTag("#clanLocations", ClanTags.locations, "#locationTags");
        ClanTags.addTag("#clanSurnames", ClanTags.surnames, "#surnameTags");
        ClanTags.bindRemoveHandler(ClanTags.locations, "#locationTags");
        ClanTags.bindRemoveHandler(ClanTags.surnames, "#surnameTags");
    }

    // ========== Form Handlers ==========
    $("#addClanForm, #editClanForm").on("submit", function (e) {
        e.preventDefault();

        const form = $(this);
        const isEdit = form.attr("id") === "editClanForm";

        const data = {
            action: isEdit ? "update_clan" : "add_clan",
            nonce: family_tree.nonce,
            clan_id: form.find("[name='clan_id']").val() || "",
            clan_name: form.find("[name='clan_name']").val(),
            description: form.find("[name='description']").val(),
            origin_year: form.find("[name='origin_year']").val(),
            locations: ClanTags.locations,
            surnames: ClanTags.surnames
        };

        // Validation
        const errors = ClanValidation.validate(data);
        if (errors.length > 0) {
            ClanUI.showAlert(errors.join("\n"), "error");
            return;
        }

        // AJAX request
        $.post(family_tree.ajax_url, data, function (response) {
            if (response.success) {
                ClanUI.showAlert(isEdit ? "Clan updated successfully!" : "Clan added successfully!", "success");
                setTimeout(() => window.location.href = "/browse-clans", 1000);
            } else {
                ClanUI.showAlert(response.data || "Something went wrong!", "error");
            }
        });
    });

    // ========== Delete Clan ==========
    $(document).on("click", ".btn-delete", function () {
        const clanId = $(this).data("id");
        if (!confirm("Are you sure you want to delete this clan?")) return;

        $.post(family_tree.ajax_url, {
            action: "delete_clan",
            nonce: family_tree.nonce,
            clan_id: clanId
        }, function (response) {
            if (response.success) {
                ClanUI.showAlert("Clan deleted successfully!", "success");
                setTimeout(() => window.location.reload(), 1000);
            } else {
                ClanUI.showAlert(response.data || "Failed to delete clan", "error");
            }
        });
    });
});


// ========== Validation ==========
const ClanValidation = {
    validate(data) {
        const errors = [];
        if (!data.clan_name || data.clan_name.trim().length < 2)
            errors.push("Clan name must be at least 2 characters long.");
        if (!data.locations || data.locations.length === 0)
            errors.push("Please add at least one location.");
        if (!data.surnames || data.surnames.length === 0)
            errors.push("Please add at least one surname.");
        return errors;
    }
};


// ========== UI Helpers ==========
const ClanUI = {
    showAlert(message, type = "success") {
        const alertBox = document.createElement("div");
        alertBox.className = `clan-alert ${type}`;
        alertBox.textContent = message;
        Object.assign(alertBox.style, {
            position: "fixed",
            top: "20px",
            right: "20px",
            background: type === "success" ? "#d4edda" : "#f8d7da",
            color: type === "success" ? "#155724" : "#721c24",
            border: `1px solid ${type === "success" ? "#c3e6cb" : "#f5c6cb"}`,
            padding: "12px 16px",
            borderRadius: "5px",
            zIndex: "9999",
            boxShadow: "0 2px 6px rgba(0,0,0,0.15)"
        });

        document.body.appendChild(alertBox);
        setTimeout(() => alertBox.remove(), 3000);
    }
};
