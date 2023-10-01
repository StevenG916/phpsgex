<h3>Welcome to PhpSgeX <?=SGEXVER?> Installer</h3>

PhpVersion: <?=phpversion()?> 
<?php if( self::PhpOk() ){ ?>
    <span class="green">OK<span>
<?php } else { ?>
    <span class="red">Php 5.3.2 or later is required!</span>
<?php return; }

if( isset($_SESSION["err"]) ){ ?>
    <script>alert("<?=$_SESSION["err"]?>")</script>
<?php } ?> <br>

<form id="frmLanguage" method="post">
    Language: <select name="language" onchange="document.getElementById('frmLanguage').submit()">
<?php
foreach( GetLanguages() as $langOpt )
    echo "<option ".( $_SESSION["lang"] == $langOpt ? "selected" : "" ).">$langOpt</option>";
?>
</select>
</form>

<h3>Database Info</h3>
<form method="post" action="?pg=Installer&act=Install">
    <table border="1">
        <tr><td>Database Url:</td><td><input type="text" name="dbHost" placeholder="localhost" required></td></tr>
        <tr><td>Database name*:</td><td><input type="text" name="dbName" required></td></tr>
        <tr><td>Table prefix:</td><td><input type="text" name="dbTablePrefix"></td></tr>
        <tr><td>User:</td><td><input type="text" name="dbUser" placeholder="root" required></td></tr>
        <tr><td>Password:</td><td><input type="password" name="dbPassword"></td></tr>
    </table>
    <i>* if not existing a database will be created (if there are enough permissisons)</i>

<h3>Game Info</h3>
<table border="1">
    <tr><td>Game name:</td><td><input type="text" name="gameName" required></td></tr>
    <tr><td>Game short description:</td><td><input type="text" name="gameShortDescription"></td></tr>
    <tr><td>Game description:</td><td><textarea name="gameDescription"></textarea></td></tr>
    <tr><td>Map system:</td><td><select name="mapSystem">
                <option value="1" selected>1) Plain (like Ogame)</option>
                <option value="2">2) 2D map (like Travian, Tribals)</option>
            </select></td></tr>
    <tr><td>City System:</td><td><select name="citySystem">
                <option value="1" selected>1) Fixed (like Ogame, Tribals)</option>
                <option value="2">2) Positional (like Travian, Ikariam)</option>
            </select></td></tr>
</table>

<input type="submit" value="Install">

</form>
