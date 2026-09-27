<?php
$content = file_get_contents('resources/views/admins/admin/audit/sync.blade.php');

$search = <<<EOD
        function onAcademicYearChange() {
            var yearId = document.getElementById('select-filter-academic-year').value;
            var billSelect = document.getElementById('select-filter-bill-type');
            var options = billSelect.querySelectorAll('option');

            var currentSelectedValid = false;

            options.forEach(function(opt) {
                if (!opt.value) {
                    opt.style.display = ''; // "Semua Jenis Tagihan" always visible
                    if (billSelect.value === opt.value) currentSelectedValid = true;
                    return;
                }
                var optYearId = opt.getAttribute('data-academic-year-id');
                if (!yearId || optYearId === yearId) {
                    opt.style.display = '';
                    if (billSelect.value === opt.value) currentSelectedValid = true;
                } else {
                    opt.style.display = 'none';
                }
            });

            if (!currentSelectedValid) {
                billSelect.value = '';
            }
            
            fetchMasterDiff(document.getElementById('current-merge-module').value);
        }
EOD;

$replace = <<<EOD
        function filterBillTypesDropdown() {
            var yearId = document.getElementById('select-filter-academic-year') ? document.getElementById('select-filter-academic-year').value : '';
            var schoolId = document.getElementById('select-filter-school') ? document.getElementById('select-filter-school').value : '';
            var billSelect = document.getElementById('select-filter-bill-type');
            if(!billSelect) return;
            var options = billSelect.querySelectorAll('option');

            var currentSelectedValid = false;

            options.forEach(function(opt) {
                if (!opt.value) {
                    opt.style.display = ''; // "Semua Jenis Tagihan" always visible
                    if (billSelect.value === opt.value) currentSelectedValid = true;
                    return;
                }
                
                var optYearId = opt.getAttribute('data-academic-year-id');
                var optSchoolId = opt.getAttribute('data-school-id');
                
                var matchYear = !yearId || optYearId === yearId;
                var matchSchool = !schoolId || !optSchoolId || optSchoolId === schoolId;

                if (matchYear && matchSchool) {
                    opt.style.display = '';
                    if (billSelect.value === opt.value) currentSelectedValid = true;
                } else {
                    opt.style.display = 'none';
                }
            });

            if (!currentSelectedValid) {
                billSelect.value = '';
            }
        }

        function onAcademicYearChange() {
            filterBillTypesDropdown();
            fetchMasterDiff(document.getElementById('current-merge-module').value);
        }
EOD;

$content = str_replace(str_replace("\r\n", "\n", $search), $replace, str_replace("\r\n", "\n", $content));

file_put_contents('resources/views/admins/admin/audit/sync.blade.php', $content);
echo "Done 2\n";
