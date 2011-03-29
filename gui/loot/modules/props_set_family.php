<div class="module mod-info">
    <h1 class="h-info">Family Set Bonuses</h1>
    <ul class="list-iteminfo family">
        <?php
            try {
                if (isset($this->db_family["props"]) && (!empty($this->db_family["props"]))) {
                    foreach($this->db_family["props"] as $row) {
                        echo "<li>{$row["translation"]} ({$row["req_equip"]} Equipped)</li>\n";
                    }
                } else {
                    throw new Exception("No set bonuses found.");
                }
            } catch (Exception $e) {
                echo "<p class='module mod-notify mod-warning'>" . $e->getMessage(), "</p>\n";
            }
        ?>
    </ul>
</div>
