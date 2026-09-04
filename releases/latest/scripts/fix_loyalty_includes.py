#!/usr/bin/env python3
import os

base = "/Users/ralphmore/Sites/localhost/martpoint retail/application/views/loyalty"
files = ["settings.php", "bonus_rules.php", "points_history.php", "referral_program.php"]

for fname in files:
    fpath = os.path.join(base, fname)
    with open(fpath, "r") as f:
        content = f.read()
    content = content.replace('include"../comman/code_css.php";', 'include APPPATH . "views/comman/code_css.php";')
    content = content.replace('include"../sidebar.php";', 'include APPPATH . "views/sidebar.php";')
    content = content.replace('include"../footer.php";', 'include APPPATH . "views/footer.php";')
    with open(fpath, "w") as f:
        f.write(content)
    print("Fixed:", fname)
