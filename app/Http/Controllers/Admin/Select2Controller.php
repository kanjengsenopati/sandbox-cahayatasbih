<?php

namespace App\Http\Controllers\Admin;

use App\Models\Bank;
use App\Models\Item;
use App\Models\User;
use App\Models\Study;
use App\Models\School;
use App\Models\Student;
use App\Models\BillItem;
use App\Models\Semester;
use App\Models\Classroom;
use App\Models\AcademicYear;
use App\Models\CategoryItem;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class Select2Controller extends Controller
{
    public function students(\Illuminate\Http\Request $request) {
        $request->merge(['data_type' => 'STUDENT']);
        return $this->index($request);
    }

    public function index(Request $request)
    {
        $data = match ($request->data_type) {
            'USER' => $this->user($request),
            'STUDENT' => $this->student($request),
            'CLASSROOM_BY_SCHOOL' => $this->classroomBySchool($request),
            'CATEGORY_ITEM' => $this->categoryItem($request),
            'ITEM' => $this->item($request),
            'BILL_ITEM' => $this->billItem($request),
            'ACADEMIC_YEAR' => $this->academicYear($request),
            'STUDY' => $this->study($request),
            'SEMESTER' => $this->semester($request),
            'STUDENT_BY_SCHOOL' => $this->studentBySchool($request),
            'STUDENT_ACTIVE_BY_SCHOOL' => $this->studentActiveBySchool($request),
            'BANK' => $this->bank($request),
        };
        return response()->json($data);
    }

    public function user($request)
    {
        $search = strtolower(trim($request->search ?? ''));
        $cleanSearch = preg_replace('/[^0-9]/', '', $search);

        $users = User::withCount('student')
            ->where(function ($q) use ($search, $cleanSearch) {
                $q->whereRaw('LOWER(name) like ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(phone) like ?', ['%' . $search . '%']);

                if (!empty($cleanSearch)) {
                    $q->orWhereRaw("REPLACE(REPLACE(REPLACE(phone, ' ', ''), '-', ''), '+62', '0') like ?", ['%' . $cleanSearch . '%']);
                }
            })
            ->take(60)
            ->get();

        // Group by normalized name + normalized phone to merge duplicates
        $deduped = $users->groupBy(function ($u) {
            $normName = strtolower(trim($u->name));
            $normPhone = preg_replace('/[^0-9]/', '', $u->phone ?? '');
            return $normName . '|' . $normPhone;
        })->map(function ($group) {
            return $group->sort(function ($a, $b) {
                if ($a->student_count !== $b->student_count) {
                    return $b->student_count <=> $a->student_count;
                }
                $statusScore = function ($status) {
                    return match ($status) {
                        'JAMAAH' => 3,
                        'MUKIMIN' => 2,
                        default => 1
                    };
                };
                $scoreA = $statusScore($a->jamaah_status);
                $scoreB = $statusScore($b->jamaah_status);
                if ($scoreA !== $scoreB) {
                    return $scoreB <=> $scoreA;
                }
                return strcmp($b->created_at ?? $b->id, $a->created_at ?? $a->id);
            })->first();
        })->values()->take(30);

        return $deduped;
    }

    public function student($request)
    {
        $search = strtolower(trim($request->q ?: ($request->search ?? '')));
        $schoolId = $request->school_id;

        $query = Student::with(['classroom.school'])
            ->where(function ($q) {
                $q->where('status', '!=', Student::STATUS_DROPPED_OUT)
                  ->orWhereHas('bills', function ($bQ) {
                      $bQ->where('status', \App\Models\Bill::STATUS_UNPAID);
                  });
            });

        if (!empty($schoolId)) {
            $query->where(function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)
                  ->orWhereHas('classroom', function ($cQ) use ($schoolId) {
                      $cQ->where('school_id', $schoolId);
                  });
            });
        } else {
            $query->hasSchool();
        }

        if (!empty($search)) {
            $query->where(function ($sq) use ($search) {
                $sq->whereRaw('LOWER(name) like ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(nis) like ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(nisn) like ?', ['%' . $search . '%']);
            });
        }

        $students = $query->orderBy('name')
            ->take(50)
            ->get();

        return $students->map(function ($student) {
            $className = $student->classroom?->name ?? '-';
            $genderText = $student->gender === 'L' ? 'Putra' : ($student->gender === 'P' ? 'Putri' : '');
            $nisText = $student->nis ? " [{$student->nis}]" : '';
            $classText = $className !== '-' ? " (Kelas {$className})" : '';
            
            return [
                'id' => $student->id,
                'name' => $student->name,
                'nis' => $student->nis ?? '-',
                'gender' => $student->gender,
                'classroom_name' => $className,
                'classroom_id' => $student->classroom_id,
                'school_name' => $student->classroom?->school?->name ?? '-',
                'text' => $student->name . $nisText . $classText . ($genderText ? " - {$genderText}" : ''),
            ];
        });
    }

    public function item($request)
    {
        $outletId = $request->outlet_id ?: auth()->user()->outlet_id;
        return Item::where(function($query) use ($request) {
                $query->whereRaw('LOWER(name) like ?', ['%' . strtolower($request->search) . '%'])
                    ->orWhereRaw('LOWER(code) like ?', ['%' . strtolower($request->search) . '%']);
            })
            ->whereIsActive(true)
            ->when($outletId, function($q) use ($outletId) {
                $q->where('outlet_id', $outletId);
            })
            ->take(10)
            ->get();
    }

    public function categoryItem($request)
    {
        $outletId = $request->outlet_id ?: auth()->user()->outlet_id;
        return CategoryItem::where(function($query) use ($request) {
                $query->whereRaw('LOWER(name) like ?', ['%' . strtolower($request->search) . '%'])
                    ->orWhereRaw('LOWER(code) like ?', ['%' . strtolower($request->search) . '%']);
            })
            ->when($outletId, function($q) use ($outletId) {
                $q->where('outlet_id', $outletId);
            })
            ->take(10)
            ->get();
    }

    public function billItem($request)
    {
        return BillItem::whereRaw('LOWER(name) like ?', ['%' . strtolower($request->search) . '%'])
            ->take(10)
            ->get();
    }

    public function classroomBySchool($request)
    {
        return Classroom::where('school_id', $request->school_id)->orderByRaw(\App\Helpers\DbCompat::classroomOrder())->get();
    }

    public function academicYear($request)
    {
        return AcademicYear::whereRaw('LOWER(name) like ?', ['%' . strtolower($request->search) . '%'])
            ->take(10)
            ->get();
    }

    public function study($request)
    {
        return Study::whereRaw('LOWER(name) like ?', ['%' . strtolower($request->search) . '%'])
            ->take(10)
            ->get();
    }

    public function semester($request)
    {
        return Semester::whereRaw('LOWER(name) like ?', ['%' . strtolower($request->search) . '%'])
            ->orderBy('order')
            ->take(10)
            ->get();
    }

    public function studentBySchool($request)
    {
        $academicYearId = $request->academic_year_id;
        $schoolId = $request->school_id;
        $search = strtolower(trim($request->search ?? ''));

        // Eager load relasi yang dibutuhkan
        $eagerRelations = ['classroom'];
        if ($academicYearId) {
            $eagerRelations['classroomHistories'] = function ($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId)->with('classroom')->limit(1);
            };
        }

        $query = Student::with($eagerRelations);

        if (!empty($schoolId)) {
            $query->where(function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)
                  ->orWhereHas('classroom', function ($cQ) use ($schoolId) {
                      $cQ->where('school_id', $schoolId);
                  });
            });
        }

        $query->where(function ($q) {
            $q->where('status', '!=', Student::STATUS_DROPPED_OUT)
              ->orWhereHas('bills', function ($bQ) {
                  $bQ->where('status', \App\Models\Bill::STATUS_UNPAID);
              });
        });

        if (!empty($search)) {
            $query->where(function ($sq) use ($search) {
                $sq->whereRaw('LOWER(name) like ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(nis) like ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(nisn) like ?', ['%' . $search . '%']);
            });
        }

        $students = $query->hasSchool()
            ->orderBy('name')
            ->take(100)
            ->get();

        return $students->map(function ($student) use ($academicYearId) {
            if ($academicYearId && $student->relationLoaded('classroomHistories')) {
                $history = $student->classroomHistories->first();
                $historyClass = $history?->classroom;
                if ($historyClass && strtoupper($historyClass->name) !== 'PONDOK') {
                    $student->resolved_classroom_name = $historyClass->name;
                    return $student;
                }
            }
            $student->resolved_classroom_name = $student->classroom?->name ?? '';
            return $student;
        });
    }

    public function bank($request)
    {
        return Bank::whereRaw('LOWER(name) like ?', ['%' . strtolower($request->search) . '%'])
            ->take(10)
            ->where('is_active', true)
            ->get();
    }

    public function studentActiveBySchool($request)
    {
        $academicYearId = $request->academic_year_id;
        $schoolId = $request->school_id;
        $search = strtolower(trim($request->search ?? ''));

        $eagerRelations = ['classroom'];
        if ($academicYearId) {
            $eagerRelations['classroomHistories'] = function ($q) use ($academicYearId) {
                $q->where('academic_year_id', $academicYearId)->with('classroom')->limit(1);
            };
        }

        $query = Student::with($eagerRelations);

        if (!empty($schoolId)) {
            $query->where(function ($q) use ($schoolId) {
                $q->where('school_id', $schoolId)
                  ->orWhereHas('classroom', function ($cQ) use ($schoolId) {
                      $cQ->where('school_id', $schoolId);
                  });
            });
        }

        if (!empty($search)) {
            $query->where(function ($sq) use ($search) {
                $sq->whereRaw('LOWER(name) like ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(nis) like ?', ['%' . $search . '%'])
                  ->orWhereRaw('LOWER(nisn) like ?', ['%' . $search . '%']);
            });
        }

        $students = $query->hasSchool()
            ->where('status', Student::STATUS_ACTIVE)
            ->orderBy('name')
            ->take(100)
            ->get();

        return $students->map(function ($student) use ($academicYearId) {
            if ($academicYearId && $student->relationLoaded('classroomHistories')) {
                $history = $student->classroomHistories->first();
                $historyClass = $history?->classroom;
                if ($historyClass && strtoupper($historyClass->name) !== 'PONDOK') {
                    $student->resolved_classroom_name = $historyClass->name;
                    return $student;
                }
            }
            $student->resolved_classroom_name = $student->classroom?->name ?? '';
            return $student;
        });
    }
}
