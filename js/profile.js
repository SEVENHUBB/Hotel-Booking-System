document.addEventListener("DOMContentLoaded", function () {

    const input = document.getElementById("profileImage");
    const preview = document.getElementById("previewImg");

    if (input) {
        input.addEventListener("change", function () {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function () {
                    preview.src = reader.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }

});
