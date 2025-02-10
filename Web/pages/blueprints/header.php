<div id=enTete>
    <img id=icon src= <?php echo $chemin_absolu . 'images/Eye.jpg' ?>>
    <div id=divTitre>
        <p class=TitrePrincipal> Les Chroniques de la Faille </p>
        <p class=TitrePrincipal> Les mondes oubliés </p>
    </div>

    <nav id=nav>
        <ul class=menu>
            <li class=menu-item>
                <div id=liAccueil class=divLi onclick=window.location.href='./Accueil.php'>
                    <a> Accueil </a>
                    <img class=small-icon src="../images/petite_img/fleche-deroulante.png">
                </div>


                <ul class="dropdown">
                    <li>
                        <div class=liIntro onclick=window.location.href='./Intro.php'>
                            <a> Intro </a>
                        </div>
                    </li>
                    <li>
                        <div class=liLettre onclick=window.location.href='./Lettre.php'>
                            <a> Lettre </a>
                        </div>
                    </li>
                </ul>
            </li>



            <li class=menu-item>
                <div id=liRaces class=divLi onclick=window.location.href='./Races.php'>
                    <a> Races </a>
                    <img class=small-icon src="../images/petite_img/fleche-deroulante.png">
                </div>


                <ul class="dropdown">
                    <li>
                        <div class=liIntro onclick=window.location.href='./Intro.php'>
                            <a> Intro </a>
                        </div>
                    </li>
                    <li>
                        <div class=liLettre onclick=window.location.href='./Lettre.php'>
                            <a> Lettre </a>
                        </div>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>



    <div id=divacceuil>
        <div id=acceuil onclick=window.location.href='<?php echo $chemin_absolu . "pages/Accueil.php" ?>'>
            <a> La Grande Librairie </a>
        </div>
    </div>
</div>