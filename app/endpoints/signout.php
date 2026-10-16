<?php
if (is_post()) { check_csrf(); logout_user(); }
redirect('/');
