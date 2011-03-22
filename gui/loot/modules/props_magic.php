<div class="module mod-info">
    <h1 class="h-info">Magical Properties</h1>
    <table class="table-info magic">
        <tbody>
            <?php
                try {
                    if (isset($this->db_item["prop_magic"]) && (!empty($this->db_item["prop_magic"]))) {
                        foreach($this->db_item["prop_magic"] as $row) {
                            echo "<tr>";
                                echo "<td>{$row["translation"]}";
                            echo "</tr>\n";
                        }
                    } else {
                        throw new Exception("No magic properties found.");
                    }
                }
                catch (Exception $e) {
                    echo "<p>" . $e->getMessage(), "</p>\n";
                }
            ?>
        </tbody>
    </table>
</div>
