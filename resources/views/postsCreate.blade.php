<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Laravel 12 Summernote Editor Image Upload Example</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.css" />
</head>
      
<body>
<div class="container">
    <div class="card mt-5">
        <h3 class="card-header p-3">Laravel 12 Summernote Editor Image Upload Example </h3>
        <div class="card-body"> 
            <form method="post" action="{{ route('posts.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="form-group">
                    <label>Title</label>
                    <input type="text" name="title" class="form-control" />
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="summernote" name="body"></textarea>
                </div>
                <div class="form-group mt-2">
                    <button type="submit" class="btn btn-success btn-block">Publish</button>
                </div>
            </form>
        </div>
    </div>
</div>
       
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0/dist/js/bootstrap.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/summernote/0.8.20/summernote-bs5.min.js"></script>
    
<script type="text/javascript">
    $(document).ready(function () {
        $('#summernote').summernote({
            height: 300,
        });
    });
</script>
        
</body>
</html>