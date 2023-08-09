<?php
namespace app\admin\controller;

use app\admin\repository\ActivityRepository;
use app\admin\repository\DashboardRepository;
use app\admin\repository\DeviceRepository;
use think\App;
use think\facade\Request;

class Index extends Auth
{
    public $repository;
    protected $deviceRepository;
    protected $activityRepository;

    public function __construct(App $app, Request $request)
    {
        parent::__construct($app, $request);
        $this->repository = new DashboardRepository();
        $this->deviceRepository = new DeviceRepository();
        $this->activityRepository = new ActivityRepository();
    }

    public function index(Request $request)
    {
        $lists = $this->repository->getStatusData($request::param());
        $activities = $this->activityRepository->getAll();
        $deviceWhere = [];
        if (!empty($request::param('activity_id'))) {
            $deviceWhere[] = ['activity_id', '=', $request::param('activity_id')];
        }
        $devices = $this->deviceRepository->getAll($deviceWhere);
        return view("", compact("lists", 'activities', 'devices'));
    }
}
