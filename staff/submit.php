<?php
include '../config.php';
session_start();

if (!isset($_SESSION['step1']['student_id'])) {
    die("Session expired. Please restart the placement form.");
}

$student_id = $_SESSION['step1']['student_id'];

try {
    $pdo->beginTransaction();

    // 🔹 Step 1: placement_initial - check if record exists
    $stmtCheck1 = $pdo->prepare("SELECT id FROM placement_initial WHERE student_id = ?");
    $stmtCheck1->execute([$student_id]);
    $initialData = $stmtCheck1->fetch(PDO::FETCH_ASSOC);

    if ($initialData) {
        // 🔄 Update existing
        $initial_id = $initialData['id'];
        $stmt1 = $pdo->prepare("UPDATE placement_initial SET 
            status = ?, date_of_joining = ?, designation = ?, salary_per_month = ?, other_perks = ?, 
            organization_name = ?, city = ?, organization_address = ?, contact_person = ?, office_contact_number = ?, remarks = ?
            WHERE student_id = ?");
        $stmt1->execute([
            $_SESSION['step1']['status'],
            $_SESSION['step1']['date_of_joining'] ?? null,
            $_SESSION['step1']['designation'] ?? null,
            $_SESSION['step1']['salary_per_month'] ?? null,
            $_SESSION['step1']['other_perks'] ?? null,
            $_SESSION['step1']['organization_name'] ?? null,
            $_SESSION['step1']['city'] ?? null,
            $_SESSION['step1']['organization_address'] ?? null,
            $_SESSION['step1']['contact_person'] ?? null,
            $_SESSION['step1']['office_contact_number'] ?? null,
            $_SESSION['step1']['remarks'] ?? null,
            $student_id
        ]);
    } else {
        // ➕ Insert new
        $stmt1 = $pdo->prepare("INSERT INTO placement_initial 
            (student_id, status, date_of_joining, designation, salary_per_month, other_perks, organization_name, city, organization_address, contact_person, office_contact_number, remarks) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt1->execute([
            $student_id,
            $_SESSION['step1']['status'],
            $_SESSION['step1']['date_of_joining'] ?? null,
            $_SESSION['step1']['designation'] ?? null,
            $_SESSION['step1']['salary_per_month'] ?? null,
            $_SESSION['step1']['other_perks'] ?? null,
            $_SESSION['step1']['organization_name'] ?? null,
            $_SESSION['step1']['city'] ?? null,
            $_SESSION['step1']['organization_address'] ?? null,
            $_SESSION['step1']['contact_person'] ?? null,
            $_SESSION['step1']['office_contact_number'] ?? null,
            $_SESSION['step1']['remarks'] ?? null
        ]);
        $initial_id = $pdo->lastInsertId();
    }

    // 🔹 Step 2: placement_second_stage - only if status is 'No'
    $second_id = null;
    if ($_SESSION['step1']['status'] === 'No' && isset($_SESSION['step2'])) {
        $stmtCheck2 = $pdo->prepare("SELECT id FROM placement_second_stage WHERE student_id = ?");
        $stmtCheck2->execute([$student_id]);
        $secondData = $stmtCheck2->fetch(PDO::FETCH_ASSOC);

        if ($secondData) {
            // 🔄 Update
            $second_id = $secondData['id'];
            $stmt2 = $pdo->prepare("UPDATE placement_second_stage SET 
                placement_initial_id = ?, status = ?, date_of_joining = ?, designation = ?, salary_per_month = ?, other_perks = ?, 
                organization_name = ?, city = ?, organization_address = ?, contact_person = ?, office_contact_number = ?, remarks = ?
                WHERE student_id = ?");
            $stmt2->execute([
                $initial_id,
                $_SESSION['step2']['status'],
                $_SESSION['step2']['date_of_joining'] ?? null,
                $_SESSION['step2']['designation'] ?? null,
                $_SESSION['step2']['salary_per_month'] ?? null,
                $_SESSION['step2']['other_perks'] ?? null,
                $_SESSION['step2']['organization_name'] ?? null,
                $_SESSION['step2']['city'] ?? null,
                $_SESSION['step2']['organization_address'] ?? null,
                $_SESSION['step2']['contact_person'] ?? null,
                $_SESSION['step2']['office_contact_number'] ?? null,
                $_SESSION['step2']['remarks'] ?? null,
                $student_id
            ]);
        } else {
            // ➕ Insert
            $stmt2 = $pdo->prepare("INSERT INTO placement_second_stage 
                (student_id, placement_initial_id, status, date_of_joining, designation, salary_per_month, other_perks, organization_name, city, organization_address, contact_person, office_contact_number, remarks) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt2->execute([
                $student_id,
                $initial_id,
                $_SESSION['step2']['status'],
                $_SESSION['step2']['date_of_joining'] ?? null,
                $_SESSION['step2']['designation'] ?? null,
                $_SESSION['step2']['salary_per_month'] ?? null,
                $_SESSION['step2']['other_perks'] ?? null,
                $_SESSION['step2']['organization_name'] ?? null,
                $_SESSION['step2']['city'] ?? null,
                $_SESSION['step2']['organization_address'] ?? null,
                $_SESSION['step2']['contact_person'] ?? null,
                $_SESSION['step2']['office_contact_number'] ?? null,
                $_SESSION['step2']['remarks'] ?? null
            ]);
            $second_id = $pdo->lastInsertId();
        }
    }

    // 🔹 Step 3: placement_final_stage - no remarks handled here
    if (isset($_SESSION['step3']) && $_SESSION['step2']['status'] === 'Not Agreed') {
        $stmtCheck3 = $pdo->prepare("SELECT id FROM placement_final_stage WHERE student_id = ?");
        $stmtCheck3->execute([$student_id]);
        $finalData = $stmtCheck3->fetch(PDO::FETCH_ASSOC);

        if ($finalData) {
            // 🔄 Update
            $stmt3 = $pdo->prepare("UPDATE placement_final_stage SET 
                placement_second_stage_id = ?, date_of_joining = ?, designation = ?, salary_per_month = ?, other_perks = ?, 
                organization_name = ?, city = ?, organization_address = ?, contact_person = ?, office_contact_number = ?
                WHERE student_id = ?");
            $stmt3->execute([
                $second_id,
                $_SESSION['step3']['date_of_joining'] ?? null,
                $_SESSION['step3']['designation'] ?? null,
                $_SESSION['step3']['salary_per_month'] ?? null,
                $_SESSION['step3']['other_perks'] ?? null,
                $_SESSION['step3']['organization_name'] ?? null,
                $_SESSION['step3']['city'] ?? null,
                $_SESSION['step3']['organization_address'] ?? null,
                $_SESSION['step3']['contact_person'] ?? null,
                $_SESSION['step3']['office_contact_number'] ?? null,
                $student_id
            ]);
        } else {
            // ➕ Insert
            $stmt3 = $pdo->prepare("INSERT INTO placement_final_stage 
                (student_id, placement_second_stage_id, date_of_joining, designation, salary_per_month, other_perks, organization_name, city, organization_address, contact_person, office_contact_number) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt3->execute([
                $student_id,
                $second_id,
                $_SESSION['step3']['date_of_joining'] ?? null,
                $_SESSION['step3']['designation'] ?? null,
                $_SESSION['step3']['salary_per_month'] ?? null,
                $_SESSION['step3']['other_perks'] ?? null,
                $_SESSION['step3']['organization_name'] ?? null,
                $_SESSION['step3']['city'] ?? null,
                $_SESSION['step3']['organization_address'] ?? null,
                $_SESSION['step3']['contact_person'] ?? null,
                $_SESSION['step3']['office_contact_number'] ?? null
            ]);
        }
    }

    $pdo->commit();
    unset($_SESSION['step1'], $_SESSION['step2'], $_SESSION['step3']);

    echo "<div style='padding: 20px; font-family: sans-serif; color: green;'>Placement details submitted successfully!</div>";

} catch (PDOException $e) {
    $pdo->rollBack();
    echo "<div style='color: red;'>Error: " . $e->getMessage() . "</div>";
}
