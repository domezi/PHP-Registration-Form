<?php
if($_GET["site"] === "termine") {
    echo "<h1>Meine Termine</h1>";

    // add event
    if(isset($_POST["title"])) {
        $stmt = $conn->prepare("insert into events(title,start,end,hue,uid,id) values(?,?,?,?,?,NULL)");
        $stmt->bind_param("sssss",$_POST["title"],date("Y-m-d H:i:s",strtotime($_POST["start"])),date("Y-m-d H:i:s",strtotime($_POST["end"])),rand(0,360),$userRow["id"]);
        if($stmt->execute()) {
            echo "<div class='alert alert-success'>Termin wurde erstellt.</div>";
        } else {
            echo "<div class='alert alert-danger'>Termin konnte nicht erstellt werden.</div>";
        }
        $stmt->close();
    }

    echo "<div class='jumbotron'>";
    echo "<div style=font-size:1.3em;margin-bottom:10px>Termin hinzufügen</div>";
    ?>
    <form method=post>
        Beschreibung: <input placeholder="Beschreibung" value="Testtermin am abend" name=title minlength=3 required class="form-control">
        <div class="row">
            <div class=col-md-6>
               Von: <input placeholder="Start" value="03.01.2020 20:00" type=datetime name=start required class="form-control">
            </div><div class=col-md-6>
               Bis: <input placeholder="Ende" value="03.01.2020 22:00" type=datetime name=end required class="form-control">
            </div>
        </div>
        <input name=submit type=submit class='btn btn-primary' value="Termin anlegen">
    </form>
    <?php
    echo "</div>";
    $events = $conn->query("select * from events where uid = '".$userRow["id"]."'");
    if($events->num_rows) {
        echo "<table class='table table-striped'>";
        echo "<tr><th>Beschreibung</th><th>Start</th><th>Ende</th><th>Dauer</th><th></th></tr>";
        while($event = $events->fetch_object()) {
            echo "<tr><td>".$event->title."</td>";
            echo "<td>".date("d.m.Y H:i",strtotime($event->start))."</td>";
            echo "<td>".date("d.m.Y H:i",strtotime($event->end))."</td>";
            echo "<td>~".round((strtotime($event->end)-strtotime($event->start))/3600)."h</td>";
            echo "<td><a href=?site=termine&action=delete&id=".$event->id." class='btn btn-danger' ><span class='glyphicon glyphicon-trash'></span></a></td></tr>";
        }
        echo "</table>";
    } else {
        echo "Keine Termine gefunden.";
    }
    
}
