<style>
  select {
    display: inline;
    max-width: 500px;
  }

  #submitResponse {
    display: inline;
  }
</style>

<div class="input-field searchstuff">
  <select id="user">
    <option value="0" selected disabled>Select user to edit</option>
    <?php
      $cols = array("id, email, firstname, lastname");
      $db->orderBy("firstname","asc");
      $users = $db->get("users", null, $cols);
      
      foreach ($users as $user) {
        echo '<option value="'.$user['id'].'">'.$user['firstname'].' '.$user['lastname'].'</option>'."\n";
      }
    ?>
  </select>
  <button type="button" onclick="editUser()">edit</button>
  <button type="button" onclick="addUser()" hidden>new user</button>
  <div id="userInfoPanel" style="width: 500px; visibility: hidden;">
    <select id="userDataSelect">
      <option value="0" selected disabled>Data</option>
    </select>
    <label for="editField">Edit:</label><br>
    <input type="text" id="editField" name="editField">
    <input id="submitBtn" type="submit" value="Submit" onclick="submitUserEdit()">
    <div id="submitResponse"></div>
  </div>
</div>

<script type="text/javascript">
  function editUser() {
    var e = document.getElementById("user");
    var userId = e.options[e.selectedIndex].value;
    console.log(userId);

    $.ajax('../ajax/users.php', {
      type: 'POST',
      data: {edit_id: userId, get_user_data: ""},
      success: function(res) {
        var userInfo = JSON.parse(res);

        $.ajax('../ajax/users.php', {
          type: 'POST',
          data: {get_columns: "get_columns"},
          success: function(res) { 
            var cols = JSON.parse(res)[0];

            // remove ID
            cols.splice(0, 1);

            openUserInfoPanel(userInfo, cols);
          },
          error: function(xhr, status, error) {
            var err = JSON.parse(xhr.responseText);
            console.log(err.Message);
          }
        });
      },
      error: function(xhr, status, error) {
        var err = JSON.parse(xhr.responseText);
        console.log(err.Message);
      }
    });
  }

  function addUser() { // TODO
    console.log('Add user features to be implemented');
  }

  function openUserInfoPanel(userInfo, cols) {
    document.getElementById("userInfoPanel").style.visibility = 'visible';

    var select = document.getElementById("userDataSelect");

    var user = document.getElementById("user");
    user.onchange = function() {
      select.options[0].selected = true;

      editField.value = '';
      editField.placeholder = '';
      document.getElementById("submitResponse").innerHTML = '';

      editUser();
    };     

    var editField = document.getElementById("editField");
    editField.addEventListener("keyup", function(event) {
      // Number 13 is the "Enter" key on the keyboard
      if(event.keyCode === 13) {
        event.preventDefault();
        
        document.getElementById("submitBtn").click();
      }
    }); 

    cols.forEach(function(col, i) {
      var name = col['Field'];

      select.options[i + 1] = new Option(name, name);
    });

    select.onchange = function() {
      document.getElementById("submitResponse").innerHTML = '';

      var i = select.selectedIndex;

      var value = select.options[i].value;
      var curr = userInfo[value];
      var placeholder = "";

      if(curr === null || typeof curr === 'undefined') {
        curr = "";
        if(value === "password") {
          placeholder = "This field is protected. ";
        }
        else {
          placeholder = "This field is unset. ";
        }
      }

      var col = cols.find(function(elem) { 
        return elem['Field'] == value;
      });

      var type = col["Type"];
      var typeDetails = "";

      if(type === "int(11) unsigned") {
        typeDetails = "Enter an integer.";
      }
      else if(type === "varchar(255)") {
        typeDetails = "Enter various characters.";
      }
      else if(type === "tinyint(1)") {
        typeDetails = "Enter 1 for true, 0 for false.";
      }
      else if(type === "char(2)") {
        typeDetails = "Enter 2 letters.";
      }

      editField.value = curr;
      editField.placeholder = placeholder + typeDetails;
    };
  }

  function submitUserEdit() {
    var user = document.getElementById("user");
    var select = document.getElementById("userDataSelect");
    var newFieldValue = document.getElementById("editField").value;

    var i = select.selectedIndex;
    var field = select.options[i].value;
    var userId = user.options[user.selectedIndex].value;
    
    $.ajax('../ajax/users.php', {
      type: 'POST',
      data: {edit_id: userId, field: field, value: newFieldValue},
      success: function(res) { 
        res = JSON.parse(res);
        
        document.getElementById("submitResponse").innerHTML = res['message'];
      },
      error: function(xhr, status, error) {
        var err = JSON.parse(xhr.responseText);
        console.log(err.Message);
      }
    });
  }
</script>
