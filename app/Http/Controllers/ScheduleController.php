<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * ScheduleController Class responsible for schedule actions
 * @package App\Http
 */
class ScheduleController extends Controller
{
    /**
     * @var array $weekDays represents days of the week
     */
    private $weekDays = [
        1 => "Saturday",
        2 => "Sunday",
        3 => "Monday",
        4 => "Tuesday",
        5 => "Wednesday",
        6 => "Thursday",
        7 => "Friday",
    ];

    /**
     * @var int $numberOfChapters number of chapters
     */
    private $numberOfChapters = 30;

    /**
     * Responsible for scheduling course days
     * @param Request $request
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\JsonResponse|\Illuminate\Http\Response
     */
    public function schedule(Request $request)
    {
        $validator = Validator::make($request->all(), [
            "startDate" => "required|date_format:Y-m-d",
            "days" => "required|array",
            "sessions" => "required|numeric|min:1"
        ]);
        $errors = [];
        $validationErrors = $validator->errors();
        if ($validationErrors->has("startDate")) {
            $errors["errors"]["startDate"] = "Must define course start date in format (Y-m-d)";
        }
        if ($validationErrors->has("days")) {
            $errors["errors"]["days"] = "Must define course days";
        }
        if ($validationErrors->has("sessions")) {
            $errors["errors"]["sessions"] = "Number of sessions must be numeric value";
        }
        if ($errors) {
            return response($errors, 422);
        }
        $startDate = $request->get("startDate");

        $days = $request->get("days");

        $sessionsPerChapter = $request->get("sessions");

        $numberOfIterations = ceil(($sessionsPerChapter * $this->numberOfChapters) / count($days));
        $sessionsDays = [];
        $includeStartDate = false;

        $startDateDay = date("l", strtotime($startDate));
        if ($this->weekDays[$days[0]] == $startDateDay) {
            $sessionsDays[] = $startDate;
            $includeStartDate = true;
        }
        $lastDate = $startDate;
        for (; $numberOfIterations > 0; $numberOfIterations--) {
            foreach ($days as $day) {
                if ($includeStartDate) {
                    $includeStartDate = false;
                    continue;
                }
                $lastDate = date("Y-m-d", strtotime("next {$this->weekDays[$day]}", strtotime($lastDate)));
                $sessionsDays[] = $lastDate;
            }
        }
        return response()->json($sessionsDays, 200);
    }
}