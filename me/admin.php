<style>
  select {
    display: inline;
    max-width: 500px;
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
    <form action="/action_page.php">
      <select id="userDataSelect">
        <option value="0" selected disabled>Data</option>
      </select>
      <form>
        <label for="editField">Edit:</label><br>
        <input type="text" id="editField" name="editField"><br>
      </form>
      <input type="submit" value="Submit">
      <div>int(11) [Integer], varchar(255) [Alphanumberic Characters], tinyint(1) [1 for true, 0 for false], char(2) [2 Letters]</div>
    </form> 
  </div>
</div>

<script type="text/javascript">
  function addUser() {
    console.log('add');
  }

  function openUserInfoPanel(userInfo, cols) { console.log('@@ouip', userInfo);
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

      e.value = userInfo[select.options[i].value];
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
