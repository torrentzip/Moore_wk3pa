<?php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "sdc310_wk3pa";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
try {
    $conn = new mysqli($host, $user, $pass, $db);
} catch (mysqli_sql_exception $e) {
    die("Could not connect to the database: " . htmlspecialchars($e->getMessage()));
}

$message = "";
$editRow = null;

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $action   = $_POST["action"] ?? "";
    $userNo   = (int)($_POST["userno"] ?? 0);
    $fullName = trim($_POST["fullname"] ?? "");
    $birth    = trim($_POST["birthdate"] ?? "");
    $color    = trim($_POST["color"] ?? "");
    $place    = trim($_POST["place"] ?? "");
    $nick     = trim($_POST["nickname"] ?? "");

    if ($action === "delete") {
        $stmt = $conn->prepare("DELETE FROM personal_info WHERE UserNo = ?");
        $stmt->bind_param("i", $userNo);
        $stmt->execute();
        $message = "Record deleted.";
    } elseif ($fullName === "" || $birth === "" || $color === "" || $place === "" || $nick === "") {
        $message = "Please fill in all fields.";
    } elseif ($action === "add") {
        $stmt = $conn->prepare("INSERT INTO personal_info (FullName, Birthdate, FavoriteColor, FavoritePlace, Nickname) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $fullName, $birth, $color, $place, $nick);
        $stmt->execute();
        $message = "Record added.";
    } elseif ($action === "update") {
        $stmt = $conn->prepare("UPDATE personal_info SET FullName = ?, Birthdate = ?, FavoriteColor = ?, FavoritePlace = ?, Nickname = ? WHERE UserNo = ?");
        $stmt->bind_param("sssssi", $fullName, $birth, $color, $place, $nick, $userNo);
        $stmt->execute();
        $message = "Record updated.";
    }
}

if (isset($_GET["edit"])) {
    $editId = (int)$_GET["edit"];
    $stmt = $conn->prepare("SELECT * FROM personal_info WHERE UserNo = ?");
    $stmt->bind_param("i", $editId);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc();
}

$result = $conn->query("SELECT * FROM personal_info ORDER BY UserNo");
?>
<!DOCTYPE html>
<html>

<head>
    <title>Torren Moore Wk 3 Performance Assessment</title>
</head>

<body>
    <h1>Torren Moore Wk 3 Performance Assessment</h1>

    <?php if ($message !== "") { ?>
        <p><b><?php echo htmlspecialchars($message); ?></b></p>
    <?php } ?>

    <h2>Stored Personal Information</h2>
    <table border="1" cellpadding="6">
        <tr>
            <th>UserNo</th>
            <th>Name</th>
            <th>Date of Birth</th>
            <th>Favorite Color</th>
            <th>Favorite Place To Visit</th>
            <th>Nickname</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $result->fetch_assoc()) { ?>
            <tr>
                <td><?php echo (int)$row["UserNo"]; ?></td>
                <td><?php echo htmlspecialchars($row["FullName"]); ?></td>
                <td><?php echo htmlspecialchars($row["Birthdate"]); ?></td>
                <td><?php echo htmlspecialchars($row["FavoriteColor"]); ?></td>
                <td><?php echo htmlspecialchars($row["FavoritePlace"]); ?></td>
                <td><?php echo htmlspecialchars($row["Nickname"]); ?></td>
                <td>
                    <a href="?edit=<?php echo (int)$row["UserNo"]; ?>">Edit</a>
                    <form method="post" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="userno" value="<?php echo (int)$row["UserNo"]; ?>">
                        <input type="submit" value="Delete">
                    </form>
                </td>
            </tr>
        <?php } ?>
    </table>

    <h2><?php echo $editRow ? "Update Record" : "Add New Record"; ?></h2>
    <form method="post" action="index.php">
        <input type="hidden" name="action" value="<?php echo $editRow ? "update" : "add"; ?>">
        <input type="hidden" name="userno" value="<?php echo $editRow ? (int)$editRow["UserNo"] : 0; ?>">
        <p><b>Name:</b> <input type="text" name="fullname" value="<?php echo $editRow ? htmlspecialchars($editRow["FullName"]) : ""; ?>"></p>
        <p><b>Date of Birth:</b> <input type="text" name="birthdate" value="<?php echo $editRow ? htmlspecialchars($editRow["Birthdate"]) : ""; ?>"></p>
        <p><b>Favorite Color:</b> <input type="text" name="color" value="<?php echo $editRow ? htmlspecialchars($editRow["FavoriteColor"]) : ""; ?>"></p>
        <p><b>Favorite Place To Visit:</b> <input type="text" name="place" value="<?php echo $editRow ? htmlspecialchars($editRow["FavoritePlace"]) : ""; ?>"></p>
        <p><b>Nickname:</b> <input type="text" name="nickname" value="<?php echo $editRow ? htmlspecialchars($editRow["Nickname"]) : ""; ?>"></p>
        <input type="submit" value="<?php echo $editRow ? "Update Record" : "Add Record"; ?>">
        <?php if ($editRow) { ?>
            <a href="index.php">Cancel</a>
        <?php } ?>
    </form>
</body>

</html>