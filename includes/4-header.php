<header>
  <div class="navbar-fixed">
    <nav class="z-depth-1 row">
      <div class="nav-wrapper col s12">
        <a href="/archive" class="brand-logo left toolbarText">Stock Archive</a>
        <ul id="nav-mobile" class="right">     
          <li id="playerLink"><a class="toolbarText" href="/archive/player">Player</a></li>
          <li id="searchLink"><a class="toolbarText" href="/archive/search">Search</a></li>
          <!-- <li id="reviewsLink"><a class="toolbarText" href="/archive/collections">My Collections</a></li> -->
          <!-- <li id="cartsLink"><a class="toolbarText" href="/archive/carts">Carts</a></li> -->
          <li id="uploadLink"><a class="toolbarText" href="/archive/upload">Upload</a></li>
          <li id="meLink"><a class="toolbarText" href="/archive/me"><?php echo $_SESSION['nickname']; ?></a></li>
        </ul>
      </div>
    </nav>
  </div>
</header>
