<style>
  select {
    display: inline;
    max-width: 500px;
  }
</style>

<div class="input-field searchstuff">
  <select id="id">
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
  <button type="button">edit</button>
  <button type="button">new user</button>
</div>