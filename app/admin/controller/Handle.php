<?php
declare (strict_types = 1);

namespace app\admin\controller;

use app\admin\repository\HandleLogRepository;
use think\App;
use think\facade\Request;

class Handle extends Auth
{
    protected $repository;

    public function __construct(App $app, Request $request)
    {
        parent::__construct($app, $request);
        $this->repository = new HandleLogRepository();
    }

    public function index(Request $request)
    {
        $adminId = $request::has('admin_id') ? $request::param('admin_id') : 0;
        $keyword = $request::has('keyword') ? $request::param('keyword') : '';
        $admins = $this->repository->getAdmins();
        $list = $this->repository->paginate([], Request::param());
        return view('', compact('admins', 'list', 'adminId', 'keyword'));
    }
}
