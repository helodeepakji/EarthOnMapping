<?php

$current_page = 'gallery';
include 'settings/config/config.php';
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] != true) {
    header("location: login.php");
    exit;
} else {
    $userDetails = $_SESSION['userDetails'];
}

$id = base64_decode($_GET['cat_id']);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if($_FILES['image']['name'] != ''){
        $image = basename($_FILES['image']['name']);
        $uploadPath = 'upload/gallery/' . $image;
        move_uploaded_file($_FILES['image']['tmp_name'], $uploadPath);
        $check = $conn->prepare('INSERT INTO `gallery`( `category_id`, `image`) VALUES (? , ? )');
        $result = $check->execute([$id , $image]);
        if($result){
            echo "<script> window.location.href = 'image-gallery.php?cat_id=".$_GET['cat_id']."'; </script>";
        }
    }
}

$gallery = $conn->prepare("SELECT * FROM `gallery_category` WHERE `id` = ?");
$gallery->execute([$id]);
$gallery = $gallery->fetch(PDO::FETCH_ASSOC);

$images = $conn->prepare("SELECT * FROM `gallery` WHERE `category_id` = ?");
$images->execute([$id]);
$images = $images->fetchAll(PDO::FETCH_ASSOC);

?>
<?php
$title = 'Gallery || EOM ';
include 'settings/header.php';
?>
<style>
    .imagebox{
        background: white;
        padding: 15px;
        border: 1px solid black;
        border-radius: 20px;
    }
    .imagebox img{
        object-fit: cover;
        height: 180px;
    }
</style>
<div class="page-wrapper" style="margin-left: 250px;margin-top: 100px;">
    <div class="content container-fluid">
        <div class="d-flex justify-content-between">
            <h2><?php echo $gallery['title'] ?></h2>
            <a data-bs-toggle="modal" href="#myModal" class="btn btn-primary">Upload</a>
        </div>
        <div class="row">
            <?php
            foreach ($images as $value) {
                echo '
                    <div class="col-sm-2 imagebox m-2" id="row_'.$value['id'].'">
                        <i class="fas fa-trash" style="color:red;position: absolute;cursor: pointer;" onclick="deleteImage('.$value['id'].')"></i>
                        <img src="upload/gallery/' . $value['image'] . '" width="100%" alt="">
                    </div>
                    ';
            }
            ?>
        </div>
    </div>
</div>

<div class="modal" id="myModal">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Uplaod Image</h4>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-hidden="true"></button>
            </div>
            <form method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="fileInput" class="form-label file">Select a File</label>
                        <div class="input-group">
                            <input type="file" class="form-control Attachment-input" aria-label="Upload" name="image" required>
                        </div>
                    </div>

                </div>
                <div class="modal-footer">
                    <a href="#" data-bs-dismiss="modal" class="btn btn-outline-dark">Close</a>
                    <button id="modal2_btn" class="btn btn-primary" name="upload" value="upload">Upload file</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include 'settings/footer.php' ?>
<script>
    var notyf = new Notyf({ position: { x: 'right', y: 'top' } });

    $("#uploadForm").submit(function (e) {
        e.preventDefault();
        var formData = new FormData(this);
        $.ajax({
            url: 'settings/api/galleryApi.php',
            type: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            dataType: 'json',
            success: function (response) {
                location.reload();
            },
            error: function (xhr, status, error) {
                var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
                notyf.error(errorMessage);
            }
        });
    });

    function deleteImage(id) {
        $.ajax({
            url: 'settings/api/galleryApi.php',
            type: 'POST',
            data: {
                id: id,
                type: 'deleteImage'
            },
            dataType: 'json',
            success: function (response) {
                $('#row_' + id).remove();
            },
            error: function (xhr, status, error) {
                var errorMessage = xhr.responseJSON ? xhr.responseJSON.message : "Something went wrong.";
                notyf.error(errorMessage);
            }
        });
    }

    
</script>
</body>

</html>