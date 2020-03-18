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
        <option value="0" selected>Data</option>
      </select>
      <input type="submit" value="Submit">
    </form> 
  </div>
</div>

<script type="text/javascript">
  function addUser() {
    console.log('add');
  }

  function openUserInfoPanel(userInfo) {

  }

  function editUser() {
    var e = document.getElementById("user");
    var userId = e.options[e.selectedIndex].value;
    console.log(userId);

    $.ajax('../ajax/users.php', {
      type: 'POST',
      data: {userId: userId},
      success: function(res) {
        var userInfo = JSON.parse(res);

        openUserInfoPanel(userInfo);
      },
      error: function(xhr, status, error) {
        var err = JSON.parse(xhr.responseText);
        console.log(err.Message);
      }
    });
  }
</script>
