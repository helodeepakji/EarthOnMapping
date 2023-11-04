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

$gallery = $conn->prepare("SELECT * FROM `gallery_category`");
$gallery->execute();
$gallery = $gallery->fetchAll(PDO::FETCH_ASSOC);

?>
<?php
$title = 'Gallery || EOM ';
include 'settings/header.php';
?>
<div class="page-wrapper" style="margin-left: 250px;margin-top: 100px;">
    <div class="content container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="modal-header">
                        <h4 class="modal-title"> <span id="title_status">Add </span> Category</h4>
                    </div>
                    <div class="card-body">
                        <form id="uploadForm">
                            <div class="row form-row">
                                <div class="col-12 col-sm-4  m-3">
                                    <div class="form-group">
                                        <label>Title </label>
                                        <input type="text" id="title" name="title" class="form-control mt-2">
                                        <input type="hidden" id="type" name="type" value="addCategory">
                                        <input type="hidden" id="cat_id" name="id" value="">
                                    </div>
                                </div>
                                <div class="col-12 col-sm-4  m-3">
                                    <div class="form-group">
                                        <label>Cover Image </label>
                                        <input type="file" name="cover_image" class="form-control mt-2">
                                    </div>
                                </div>
                                <div class="col-12 col-sm-3" style="display: flex; align-items: center;">
                                    <button type="submit" id="submit_btn" value="Upload Image" name="submit"
                                        class="btn btn-primary w-100 mt-4">Add</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <table id="dataTable" class="display">
                <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Title</th>
                        <th scope="col">Cover Image</th>
                        <th scope="col">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $i = 1;
                    foreach ($gallery as $data) {
                        $id = base64_encode($data['id']);
                        echo '
                    <tr id="row_' . $data['id'] . '">
                      <th scope="row">' . $i . '</th>
                      <td>' . $data['title'] . '</td>
                      <td><img src="upload/gallery/' . $data['cover'] . '" width="100px"></</td>
                      <td><a class="btn btn-primary" href="image-gallery.php?cat_id='. $id .'">View</a>
                      <a class="btn btn-danger" onclick="deleteCategory(' . $data['id'] . ')">Delete</a>
                      <a class="btn btn-primary" onclick="editCategory(' . $data['id'] . ')">Edit</a></td>
                    </tr>
                  ';
                        $i++;
                    }

                    ?>
                </tbody>
            </table>
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

    function deleteCategory(id) {
        $.ajax({
            url: 'settings/api/galleryApi.php',
            type: 'POST',
            data: {
                id: id,
                type: 'deleteCategory'
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

    function editCategory(id){
        $.ajax({
            url: 'settings/api/galleryApi.php',
            type: 'GET',
            data: {
                id: id,
                type: 'getCategory'
            },
            dataType: 'json',
            success: function (response) {
                $('#title').val(response.title);
                $('#cat_id').val(response.id);
                $('#title_status').text('Edit');
                $('#type').val('updateCategory');
                $('#submit_btn').text('Update');
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