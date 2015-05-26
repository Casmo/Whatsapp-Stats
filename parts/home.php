<div class="container-fluid">
    <h1>Whatsapp stats</h1>

    <p class="lead">Generate stats from group chats.</p>
    <ul>
        <li>Export chat in Whatsapp (menu -> more -> mail chat)</li>
        <li>Upload file</li>
    </ul>
    <form method="post" enctype="multipart/form-data">
        <div class="form-group">
            <label for="file">Chatlog</label>
            <input type="file" name="chat"/>
        </div>
        <input type="submit" class="btn btn-primary" value="Upload" name="upload"/>
    </form>
</div>