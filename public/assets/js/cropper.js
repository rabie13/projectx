var previewsection = $('#image-preview-section');
var image = document.getElementById('image-preview');
var cropper;

$(document).on("change", "#user_photo", function(e){
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
    var files = e.target.files;
    var done = function (url) {
    image.src = url;
    previewsection.removeClass('d-none');
    crop();
    };
    var reader;
    var file;
    var url;

    if (files && files.length > 0) {
        file = files[0];

    if (URL) {
        done(URL.createObjectURL(file));
    } else if (FileReader) {
        reader = new FileReader();
        reader.onload = function (e) {
        done(reader.result);
        };
        reader.readAsDataURL(file);
    }
    }
});

function getBlob() {
    canvas = cropper.getCroppedCanvas({
    width: 160,
    height: 160,
    });

    canvas.toBlob(function(blob) {
        url = URL.createObjectURL(blob);
        var reader = new FileReader();
        reader.readAsDataURL(blob); 
        reader.onloadend = function() {
            var base64data = reader.result;  
            $('#imageBlob').val(base64data);
        }
    });
}

function crop() {
    cropper = new Cropper(image, {
    aspectRatio: 1,
    viewMode: 3,
    preview: '.preview'
    });
}