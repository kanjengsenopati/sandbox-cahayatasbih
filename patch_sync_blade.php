<?php
$content = file_get_contents('resources/views/admins/admin/audit/sync.blade.php');

$search1 = <<<EOD
                                              @foreach(\$billTypes ?? [] as \$bt)
                                                  <option value="{{ \$bt->id }}" data-academic-year-id="{{ \$bt->academic_year_id ?? '' }}">{{ \$bt->name }}</option>
                                              @endforeach
EOD;

$replace1 = <<<EOD
                                              @foreach(\$billTypes ?? [] as \$bt)
                                                  <option value="{{ \$bt->id }}" data-academic-year-id="{{ \$bt->academic_year_id ?? '' }}" data-school-id="{{ \$bt->school_id ?? '' }}">{{ \$bt->name }}</option>
                                              @endforeach
EOD;

$content = str_replace($search1, $replace1, $content);

$search2 = <<<EOD
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

$replace2 = <<<EOD
        function filterBillTypesDropdown() {
            var yearId = document.getElementById('select-filter-academic-year').value;
            var schoolId = document.getElementById('select-filter-school').value;
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
                var optSchoolId = opt.getAttribute('data-school-id');
                
                var matchYear = !yearId || optYearId === yearId;
                // If a bill type has no school mapped (e.g. general bills), we might want to always show it. But to fix leakage, we require a match if school is selected and it has one. Wait, if optSchoolId is empty, it's a global bill. Let's allow it if optSchoolId is empty or matches.
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

$content = str_replace($search2, $replace2, $content);

$search3 = <<<EOD
        function onSchoolFilterChange() {
            var schoolId = document.getElementById('select-filter-school').value;
            
            // Reset Classroom Selection
            document.getElementById('select-filter-classroom').value = '';
            document.getElementById('btn-classroom-text').innerText = 'Semua Kelas';

            // Filter the custom dropdown headers and columns
            document.querySelectorAll('.school-group-header').forEach(function(hdr) {
                if (!schoolId || hdr.getAttribute('data-school') === schoolId) {
                    hdr.style.display = '';
                } else {
                    hdr.style.display = 'none';
                }
            });

            fetchMasterDiff(document.getElementById('current-merge-module').value);
        }
EOD;

$replace3 = <<<EOD
        function onSchoolFilterChange() {
            var schoolId = document.getElementById('select-filter-school').value;
            
            // Reset Classroom Selection
            document.getElementById('select-filter-classroom').value = '';
            document.getElementById('btn-classroom-text').innerText = 'Semua Kelas';

            // Filter the custom dropdown headers and columns
            document.querySelectorAll('.school-group-header').forEach(function(hdr) {
                if (!schoolId || hdr.getAttribute('data-school') === schoolId) {
                    hdr.style.display = '';
                } else {
                    hdr.style.display = 'none';
                }
            });

            filterBillTypesDropdown();
            fetchMasterDiff(document.getElementById('current-merge-module').value);
        }
EOD;

$content = str_replace($search3, $replace3, $content);

file_put_contents('resources/views/admins/admin/audit/sync.blade.php', $content);
echo "Patched sync.blade.php\n";
