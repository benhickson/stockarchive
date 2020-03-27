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
    <option value="0" selected>Select User</option>
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
  <button type="button" onclick="addUser()">new user</button>
  <div id="userInfoPanel" style="width: 500px">
    <select id="userDataSelect">
      <option value="0" selected disabled>Data</option>
    </select>
    <form>
      <label for="editField">Edit:</label><br>
      <input type="text" id="editField" name="editField">
    </form>
    <input type="submit" value="Submit" onclick="submitUserEdit()">
    <div id="submitResponse"></div>
  </div>
</div>

<script type="text/javascript">
  function addUser() {
    console.log('@@add');
  }

  function submitUserEdit() {
    var user = document.getElementById("user");
    var select = document.getElementById("userDataSelect");
    var newFieldValue = document.getElementById("editField").value;

    var i = select.selectedIndex;
    var field = select.options[i].value;
    var userId = user.options[user.selectedIndex].value; console.log("@@", userId, field, newFieldValue);
    
    $.ajax('../ajax/users.php', {
      type: 'POST',
      data: {user_id: userId, field: field, value: newFieldValue},
      success: function(res) { 
        res = JSON.parse(res);
        console.log('@@edit user', res.length, res);
        document.getElementById("submitResponse").innerHTML = res['message'];
      },
      error: function(xhr, status, error) {
        var err = JSON.parse(xhr.responseText);
        console.log(err.Message);
      }
    });
  }

  function openUserInfoPanel(userInfo, cols) { console.log('@@ouip', cols);
    var select = document.getElementById("userDataSelect");

    cols.forEach(function(col) {
      var name = col['Field'];
      var type = col['Type'];

      select.options[select.options.length] = new Option(name + " // " + type, name);
    });

    select.onchange = function() {
      var i = select.selectedIndex;
      console.log('@@so', select.options[i].value);
      console.log('@@ui', userInfo[select.options[i].value]);

      var e = document.getElementById("editField");
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
      }); console.log("@@col ", col);

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

      e.value = curr;
      e.placeholder = placeholder + typeDetails;
    };
  }

  function editUser() {
    var e = document.getElementById("user");
    var userId = e.options[e.selectedIndex].value;
    console.log(userId);

    $.ajax('../ajax/users.php', {
      type: 'POST',
      data: {userId: userId},
      success: function(res) { console.log('@@', res);
        var userInfo = JSON.parse(res);

        $.ajax('../ajax/users.php', {
          type: 'POST',
          data: {get_columns: "get_columns"},
          success: function(res) {
            var cols = JSON.parse(res)[0];

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
</script>
