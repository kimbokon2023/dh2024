<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Custom Modal</title>
    
<style>
/* styles.css */
body {
    font-family: Arial, sans-serif;
}

#openModalBtn {
    padding: 10px 20px;
    font-size: 16px;
}

.modal {
    display: none;
    position: fixed;
    z-index: 1;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    overflow: auto;
    background-color: rgba(0, 0, 0, 0.4);
    padding-top: 30px;
}

.modal-content {
    background-color: #fefefe;
    margin: 5% auto;
    border-radius: 10px;
    width: 80%;
    max-width: 600px;
    animation: fadeIn 0.5s;
    box-shadow: 0 5px 15px rgba(0,0,0,0.3);
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.modal-header {
    /* background-color: #007bff; */
    background-color: #1f48d4;
    color: white;
    padding: 5px;
    border-top-left-radius: 10px;
    border-top-right-radius: 10px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.modal-title {
    font-size: 15px;
}

.close {
    color: white;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
}

.close:hover,
.close:focus {
    color: #bbb;
    text-decoration: none;
}

.modal-body {
    padding: 15px;
}

.card {
    background-color: #f9f9f9;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 5px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}


</style>	
	
</head>
<body>
    <button id="openModalBtn">Open Modal</button>

    <div id="myModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <span class="modal-title">Modal Title</span>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div class="card">
                    <p>This is a custom modal window with card content.</p>
                </div>
            </div>
        </div>
    </div>

</body>
</html>


<script>
// script.js
document.addEventListener('DOMContentLoaded', (event) => {
    // Get the modal
    var modal = document.getElementById("myModal");

    // Get the button that opens the modal
    var btn = document.getElementById("openModalBtn");

    // Get the <span> element that closes the modal
    var span = document.getElementsByClassName("close")[0];

    // When the user clicks on the button, open the modal
    btn.onclick = function() {
        modal.style.display = "block";
    }

    // When the user clicks on <span> (x), close the modal
    span.onclick = function() {
        modal.style.display = "none";
    }
});


</script>