<h3>Family Set Bonuses</h3>
<ul class="list-iteminfo family">
    <?php
        try {
            if (isset($this->props_family) && (!empty($this->props_family))) {
                foreach($this->props_family as $row) {
                    echo "<li>{$row["translation"]} ({$row["req_equip"]} Equipped)</li>\n";
                }
            } else {
                throw new Exception("No set bonuses found.");
            }
        } catch (Exception $e) {
            echo "<div class='mod mod-notify mod-warning' style='width:200px'>" . $e->getMessage(), "</div>\n";
        }
    ?>
</ul>