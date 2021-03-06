<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Module;
use App\Student;
use App\StudentModules;
use App\Cohort;

class StatisticsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('teacher', ['except' => ['getOwnMarks']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('statistics');
    }

    public function showStatistics()
    {
        //count students which aren't graduated yet
        $students = Student::where('graduated', false)->count();

        //count students who are in a certain cohort
        $cohorts = Cohort::withCount(['students as student_cohort_count' => function ($query) {
            $query->where('graduated', false);
        }])
        ->with(['students' => function ($query) {
            $query->where('graduated', false);
        }])->get();

        //count students who are either busy or finished with a certain module, also calculate the average mark
        $moduleInfo = Module::withCount(['studentModules as student_modules_count' => function ($query) {
            $query->whereNotNull('begin_date')->whereNull('approved_by');
        },
        'studentModules as finished_modules_count' => function ($query) {
            $query->whereNotNull('approved_by');
        }, 'studentModules as avg_mark' => function ($query) {
            $query->select(DB::raw('avg(mark) average'));
        },
        ])->with(['studentModules.student'])
        ->get();
 
        return [ "cohorts" => $cohorts, "student_amount" => $students, "module_info" => $moduleInfo ];
    }

    //Gets all the modules with a mark of the requested student
    public function getAverageMarks(Request $request, $id)
    {
        $student = Student::with([
            'cohort.modules.studentModules' => function ($query) use ($id) {
                $query->whereNotNull('mark')->where('student_id', $id);
            }
        ])->findOrFail($id);

        return $this->getStatistics($student, $id);
    }

    //get all modules with a mark from the active student
    public function getOwnMarks(Request $request)
    {
        $id = Student::where('user_id', Auth::user()->getID())->first()->id;
        $student = Student::with([
            'cohort.modules.studentModules' => function ($query) use ($id) {
                $query->whereNotNull('mark')->where('student_id', $id);
            }
        ])->findOrFail($id);

        return $this->getStatistics($student, $id);
    }

    //also get marks of other students and prepare the data for the chart visualization
    private function getStatistics($student, $id)
    {
        $nameArray = array();
        $averageMarkArray = array();
        $markArray = array();
        $studentModules = $student->cohort->modules;
        
        $otherStudents = Student::with([
            'cohort.modules.studentModules' => function ($query) use ($id) {
                $query->whereNotNull('mark')->where('student_id', '!=', $id);
            }
        ])->findOrFail($id);

        $otherStudentModules = $otherStudents->cohort->modules;
        
        foreach ($studentModules as $sm) {
            if ($sm->studentModules->first()) {
                array_push($nameArray, $sm->name);
                array_push($markArray, $sm->studentModules->first()->mark);
            }
        }

        //prepare the data for the Vue Chart (average, 0.0, etc)
        foreach ($otherStudentModules as $osm) {
            $avg = array();
            foreach ($osm->studentModules as $studentModule) {
                if ($studentModule->mark && $studentModule->mark != "0") {
                    array_push($avg, $studentModule->mark);
                }
            }
            if (in_array($osm->name, $nameArray)) {
                if ($avg == null) {
                    array_push($averageMarkArray, 0.0);
                } else {
                    array_push($averageMarkArray, array_sum($avg)/count($avg));
                }
            }
        }

        return ["labels" => $nameArray, "marks" => $markArray, "average_marks" => $averageMarkArray ];
    }
}
