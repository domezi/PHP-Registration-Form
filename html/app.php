<?php
if($_GET["site"] === "event_invite") {
    echo "<h1>Meine Termine</h1>";
    echo "<a href=?site=termine>Meine Termine</a> > Personen einladen<hr>";
    $res = $conn->query("select * from events where id = '".intval($_GET["id"])."'");
    if($res->num_rows) {
        $event=$res->fetch_object();
        echo "Personen einladen für <b>".$event->title.", beginnt ".date("d.m.Y H:i",strtotime($event->start))." Uhr</b>";
        echo "<hr>";
        if(isset($_GET["invite_uid"])) {
            $res = $conn->query("select * from event_has_user where uid = '".intval($_GET["invite_uid"])."' and event_id = '".intval($_GET["id"])."'");
            echo $res->num_rows == 0 && $conn->query("INSERT INTO `event_has_user` (`event_id`, `uid`, `confirmed`)
VALUES ('".intval($_GET["id"])."', '".intval($_GET["invite_uid"])."', '0' );") ? "<div class='alert alert-success'>Einladung versendet.</div>": "<div class='alert alert-danger'>Einladung fehlgeschlagen.</div>";
echo $conn->error;
        }
        $users = $conn->query("select * from users where id != '".$userRow["id"]."'");
        if($users->num_rows) {
            echo "<table class='table table-striped table-bordered'>";
            while($user = $users->fetch_object()) {
                echo "<tr><td>".$user->username."</td>";
                $event_has_user = $conn->query("select * from event_has_user where event_id = '".$event->id."' and uid = '".$user->id."'");
                if($event_has_user->num_rows === 0) {
                    echo "<td><a href=?site=event_invite&id=".$event->id."&invite_uid=".$user->id." class='btn btn-success' ><span class='glyphicon glyphicon-plus'></span> Einladung versenden</a></td>";
                } else {
                    $confirmed = $event_has_user->fetch_object()->confirmed;
                    if($confirmed=="0")
                        echo "<td><a href=?site=event_invite&id=".$event->id." class='btn btn-default disabled' ><span class='glyphicon glyphicon-time'></span> Warte auf Bestätigung</a></td>";
                    else if($confirmed=="1")
                        echo "<td><a href=?site=event_invite&id=".$event->id." class='btn btn-default disabled' ><span class='glyphicon glyphicon-check'></span> Bestätigt</a></td>";
                    else if($confirmed=="2")
                        echo "<td><a href=?site=event_invite&id=".$event->id." class='btn btn-default disabled' ><span class='glyphicon glyphicon-remove'></span> Abgelehnt</a></td>";
                }
                echo "</tr>";
            }
            echo "</table>";
        }
        else echo "Keine User gefunden.";
        echo "<hr><a href=?site=termine class='btn btn-default'><span class='glyphicon glyphicon-chevron-left'></span> Meine Termine</a>";
    } else {
        echo "Termin wurde nicht gefunden.";
    }

} else if($_GET["site"] === "cal") {
    echo "<h1>Kalender</h1>";
    echo "Alle Termine. Meine Termine und Einladungen in einer Ansicht.<hr>";
    ?>

<link href='https://unpkg.com/@fullcalendar/core@4.4.0/main.min.css' rel='stylesheet' />


  

  <link href='https://unpkg.com/@fullcalendar/daygrid@4.4.0/main.min.css' rel='stylesheet' />

  <link href='https://unpkg.com/@fullcalendar/timegrid@4.4.0/main.min.css' rel='stylesheet' />


<script src='/assets/demo-to-codepen.js'></script>

<script src='https://unpkg.com/@fullcalendar/core@4.4.0/main.min.js'></script>




  <script src='https://unpkg.com/@fullcalendar/interaction@4.4.0/main.min.js'></script>

  <script src='https://unpkg.com/@fullcalendar/daygrid@4.4.0/main.min.js'></script>

  <script src='https://unpkg.com/@fullcalendar/timegrid@4.4.0/main.min.js'></script>



  
<script>

  document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');

    var calendar = new FullCalendar.Calendar(calendarEl, {
      plugins: [ 'interaction', 'dayGrid', 'timeGrid' ],
      defaultView: 'dayGridMonth',
      defaultDate: '2020-02-07',
      header: {
        left: 'prev,next today',
        center: 'title',
        right: 'dayGridMonth,timeGridWeek,timeGridDay'
      },
      events: [
    <?php
    $events = $conn->query("select * from events where uid = '".$userRow["id"]."'");
    if($events->num_rows) {
        while($event = $events->fetch_object()) {
            echo "{title:'".str_replace("'","",$event->title)."',end:'".$event->end."',start:'".$event->start."',borderColor:'gray',backgroundColor:'gray'},";
        }
    }
    $events = $conn->query("select e.* from events e, event_has_user ehu where e.id = ehu.event_id and ehu.uid = '".$userRow["id"]."'");
    if($events->num_rows) {
        while($event = $events->fetch_object()) {
            echo "{title:'".str_replace("'","",$event->title)."',end:'".$event->end."',start:'".$event->start."',borderColor:'orangered',backgroundColor:'orangered'},";
        }
    }
    ?>
      ]
    });

    calendar.render();
  });

</script>

  <div id='calendar'></div>

    <?php
} else if($_GET["site"] === "invitations") {
    echo "<h1>Einladungen</h1>";
    echo "Termine, zu denen ich eingeladen bin.<hr>";

    if(isset($_GET["id"])) {
        $sql="update `event_has_user` set confirmed = '".intval($_GET["confirm"])."' where event_id = '".intval($_GET["id"])."' and uid = '".$userRow["id"]."'";
        echo $conn->query($sql) ? "<div class='alert alert-success'>Bestätigung versendet.</div>": "<div class='alert alert-danger'>Bestätigung fehlgeschlagen.</div>";
    }

    $ins=$conn->query("select e.*,ehu.confirmed,u.username from event_has_user ehu,users u, events e where e.id = ehu.event_id and ehu.uid = '".$userRow["id"]."' and u.id = e.uid order by ehu.confirmed desc");
    if($ins->num_rows) {
        echo "<table class='table table-striped table-bordered'>";
        while($event = $ins->fetch_object()) {
            echo "<tr>";
            echo "<td>".$event->username."</td>";
            echo "<td>".$event->title."</td>";
            echo "<td>".date("d.m.Y H:i",strtotime($event->start))."</td>";
            if(!$event->confirmed)
                echo "<td><a href=?site=invitations&id=".$event->id."&confirm=1 class='btn btn-success' ><span class='glyphicon glyphicon-plus'></span> Bestätigen</a>
                <a href=?site=invitations&id=".$event->id."&confirm=2 class='btn btn-danger' ><span class='glyphicon glyphicon-remove'></span> Ablehnen</a></td>";
            else {
                if($event->confirmed == "1")
                    echo "<td><a href=?site=invitations&id=".$event->event_id." class='btn btn-default disabled' ><span class='glyphicon glyphicon-check'></span> Bestätigt</a></td>";
                else
                    echo "<td><a href=?site=invitations&id=".$event->event_id." class='btn btn-default disabled' ><span class='glyphicon glyphicon-remove'></span> Abgelehnt</a></td>";
            }

            echo "</tr>";
        }
        echo "</table>";
    }
    else echo "Keine Einladungen.";

} else if($_GET["site"] === "termine") {
    echo "<h1>Meine Termine</h1><hr>";

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
            echo "<td><a href=?site=event_invite&id=".$event->id." class='btn btn-success' ><span class='glyphicon glyphicon-user'></span></a>";
            echo " <a href=?site=termine&action=delete&id=".$event->id." class='btn btn-danger' ><span class='glyphicon glyphicon-trash'></span></a></td></tr>";
        }
        echo "</table>";
    } else {
        echo "Keine Termine gefunden.";
    }
    
}
