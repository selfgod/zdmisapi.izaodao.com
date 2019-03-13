<?php

namespace App\HttpController;

use FastRoute\RouteCollector;

class Router extends \EasySwoole\Core\Http\AbstractInterface\Router
{
    function register(RouteCollector $route)
    {
        $route->addGroup('/lesson', function (RouteCollector $route) {
            $route->get('/recordLessonData', '/Course/Lesson/recordLessonData');
            $route->post('/saveLessonRecordLink', '/Course/Lesson/saveLessonRecordLink');
            $route->post('/delLessonRecordLink', '/Course/Lesson/delLessonRecordLink');
            $route->get('/lessonRecordUrl', '/Course/Lesson/lessonRecordUrl');
            $route->get('/open', '/Course/Lesson/openLessonList');
            $route->get('/listen', '/Course/Lesson/listenLessonList');
        });

        $route->addGroup('/employees', function (RouteCollector $route) {
            //企业微信
            $route->get('/queryEmployeesData', '/Employees/Employees/queryEmployeesData');
            $route->get('/queryEmployeesInfo', '/Employees/Employees/queryEmployeesInfo');
            $route->get('/queryZDEmployeesInfo', '/Employees/Employees/queryZDEmployeesInfo');
            $route->get('/queryDepartmentData', '/Employees/Employees/queryDepartmentData');
            $route->post('/saveEmployees', '/Employees/Employees/saveEmployees');
            $route->post('/deleteEmployees', '/Employees/Employees/deleteEmployees');
            $route->post('/leaveEmployees', '/Employees/Employees/leaveEmployees');
            $route->post('/regUser', '/Employees/Employees/regUser');
            $route->post('/updateDepartmentAll', '/Employees/Employees/updateDepartmentAll');
            $route->post('/updateUserAll', '/Employees/Employees/updateUserAll');
        });

        $route->addGroup('/Market/Launchadvent', function (RouteCollector $route) {
            $route->post('/Advertiser/add', '/Market/Launchadvent/Advertiser/add');
            $route->post('/Advertiser/update', '/Market/Launchadvent/Advertiser/update');
            $route->post('/SemChannel/add', '/Market/Launchadvent/SemChannel/add');
            $route->post('/SemChannel/update', '/Market/Launchadvent/SemChannel/update');
            $route->post('/Sem/add', '/Market/Launchadvent/Sem/add');
            $route->post('/Sem/update', '/Market/Launchadvent/Sem/update');
            $route->post('/Sem/delete', '/Market/Launchadvent/Sem/delete');
            $route->post('/Index/add', '/Market/Launchadvent/Index/add');
            $route->post('/Index/update', '/Market/Launchadvent/Index/update');
            $route->post('/Index/delete', '/Market/Launchadvent/Index/delete');
        });
        $route->get('/market/codeManager/searchOptions', '/Market/Launchadvent/CodeManager/searchOptions');
        $route->get('/market/codeManager/list', '/Market/Launchadvent/CodeManager/codesList');
        $route->post('/market/codeManager/save', '/Market/Launchadvent/CodeManager/saveCode');
        $route->post('/market/codeManager/del', '/Market/Launchadvent/CodeManager/delCode');
        $route->post('/market/codeManager/updateSourceNum', '/Market/Launchadvent/CodeManager/updateSourceNum');

        $route->addGroup('/Market/Source', function (RouteCollector $route) {
            $route->post('/generate', '/Market/Source/Generate/setGenerateSource');
            $route->post('/levelOne', '/Market/Source/LevelOne/setLevelOneSource');
            $route->post('/source', '/Market/Source/Source/setSource');
            $route->post('/addCrm', '/Market/Source/Source/addCrmData');
            $route->post('/import', '/Market/Source/Source/importCrm');
            $route->post('/assignCallIn', '/Market/Source/Source/assignCallIn');
            $route->post('/assignCallInIds', '/Market/Source/Source/assignCallInIds');
            $route->post('/getConsultExpire', '/Market/Source/Source/getConsultExpire');
        });

        $route->get('/getOnlineUsers', '/Member/User/getOnlineUsers');
        $route->post('/userTaskNotice', '/Member/Notice/userTaskRemind');
        $route->post('/userEventPush', '/Member/Notice/userEventPush');

        $route->addGroup('/teach/dataCompass', function (RouteCollector $route) {
            $route->get('/querySuspendList', '/Teach/DataCompass/SuspendList/querySuspendList');
            $route->post('/delSuspendInfo', '/Teach/DataCompass/SuspendList/delSuspendInfo');
            $route->get('/outputSuspendInfo', '/Teach/DataCompass/SuspendList/outputSuspendInfo');

            $route->get('/queryClassEndList', '/Teach/DataCompass/ClassEndList/queryClassEndList');
            $route->get('/outputClassEndInfo', '/Teach/DataCompass/ClassEndList/outputClassEndInfo');

            $route->get('/queryJoinScheduleLog', '/Teach/DataCompass/JoinScheduleLog/queryJoinScheduleLogList');
            $route->get('/outputJoinScheduleLog', '/Teach/DataCompass/JoinScheduleLog/outputJoinScheduleLog');
        });

        $route->addGroup('/teach/label', function (RouteCollector $route) {
            $route->get('/queryLabelList', '/Teach/Label/Label/queryLabelList');
        });

        $route->addGroup('/teach/identity', function (RouteCollector $route) {
            $route->get('/queryIdentityList', '/Teach/Identity/Identity/queryIdentityList');
        });

        $route->addGroup('/teach/grade', function (RouteCollector $route) {
            $route->get('/queryGradeList', '/Teach/Grade/Grade/queryGradeList');
        });

        $route->addGroup('/teach/course', function (RouteCollector $route) {
            $route->get('/queryCourseWeekList', '/Teach/Course/Course/queryCourseWeekList');
        });

        $route->addGroup('/teach/teacher', function (RouteCollector $route) {
            $route->get('/querySaTeacherList', '/Teach/Teacher/Teacher/querySaTeacherList');
            $route->get('/queryTeacherList', '/Teach/Teacher/Teacher/queryTeacherList');
        });
        $route->get('/teacher/list', '/Teach/Teacher/Teacher/teacherList');

        $route->addGroup('/setting', function (RouteCollector $route) {
            $route->get('/waitpro/list', '/setting/Waitpro/waitProject');
            $route->post('/waitpro/save', '/setting/Waitpro/saveProject');
            $route->post('/waitpro/del', '/setting/Waitpro/delProject');
        });

        //销售
        $route->addGroup('/sales', function (RouteCollector $route) {
            $route->get('/team/rankingToday', '/Sales/Manage/Team/queryRankingDay');
            $route->get('/team/rankingDuring', '/Sales/Manage/Team/queryRankingDuring');
            $route->get('/team/rankingRateDuring', '/Sales/Manage/Team/queryRankingRateDuring');
            $route->get('/team/getCustomerOrder', '/Sales/Manage/Team/getCustomerOrder');
            $route->post('/consult/setOperateLog', '/Sales/Customer/Consult/setOperateLog');
            $route->post('/team/setStandardRate', '/Sales/Manage/SalesKpi/setStandardRate');
            $route->get('/team/getStandardRate', '/Sales/Manage/SalesKpi/getStandardRate');
            $route->get('/team/salesKpi/getFields', '/Sales/Manage/SalesKpi/getFields');
            $route->get('/team/salesKpi/getSalesTeam', '/Sales/Manage/SalesKpi/getSalesTeam');
            $route->get('/team/salesKpi/getSalesKpi', '/Sales/Manage/SalesKpi/getSalesKpi');
            $route->post('/team/salesKpi/setSalesTeam', '/Sales/Manage/SalesKpi/setSalesTeam');
            $route->post('/team/salesKpi/updateTeamSalesKpi', '/Sales/Manage/SalesKpi/updateTeamSalesKpi');
            $route->post('/team/salesKpi/delTeamSalesKpi', '/Sales/Manage/SalesKpi/delTeamSalesKpi');
            $route->post('/team/salesKpi/addTeamSalesKpi', '/Sales/Manage/SalesKpi/addTeamSalesKpi');
            $route->get('/team/salesKpi/initSalesTeam', '/Sales/Manage/SalesKpi/initSalesTeam');
            $route->get('/team/salesKpi/audit', '/Sales/Manage/SalesKpi/audit');
            $route->post('/team/delStandardRateItem', '/Sales/Manage/SalesKpi/delStandardRateItem');
            $route->post('/setting/level/create', '/Sales/Setting/Level/create');
            $route->get('/setting/level/salesType', '/Sales/Setting/Level/salesType');
            $route->get('/team/businessTypeRegion', '/Sales/Setting/TeamStructure/getRegionForBusinessType');
            $route->get('/team/salesmanType', '/Sales/Setting/TeamStructure/getSalesmanTypeForBusinessType');
            $route->get('/team/structure/getDept', '/Sales/Setting/TeamStructure/getDept');
            $route->get('/team/structure/getTeam', '/Sales/Setting/TeamStructure/getTeam');
            $route->get('/team/structure/getSalesman', '/Sales/Setting/TeamStructure/getSalesman');
            $route->post('/setting/level/update', '/Sales/Setting/Level/update');
            $route->post('/manage/levelList/update', '/Sales/Manage/LevelList/update');
            $route->post('/manage/workingDays/update', '/Sales/Manage/WorkingDays/update');
            $route->post('/manage/workingDays/create', '/Sales/Manage/WorkingDays/create');
            $route->post('/setting/structure/add', '/Sales/Setting/TeamStructure/addStructure');
            $route->post('/setting/structure/update', '/Sales/Setting/TeamStructure/updateStructure');
            $route->post('/setting/structure/delete', '/Sales/Setting/TeamStructure/delStructure');
        });

        $route->get('/testQuery', '/Sales/Test/testQuery');


        $route->addGroup('/sales', function (RouteCollector $route) {
            $route->get('/getCCLastMemo', '/Sales/Memo/Memo/getCCLastMemo');
            $route->get('/getSALastMemo', '/Sales/Memo/Memo/getSALastMemo');
        });

        $route->get('/compass/monthSas', '/Teach/DataCompass/Complex/monthComplexSas');

        $route->post('/compass/qingning', '/Teach/DataCompass/QinNing/dataSas');

        $route->addGroup('/listenLesson', function (RouteCollector $route) {
            $route->get('/info', '/Operator/Listen/getListenCourse');
            $route->post('/set', '/Operator/Listen/setListenCourse');
        });

        $route->addGroup('/permission', function (RouteCollector $route) {
            $route->post('/addRole', '/Permission/Permission/addRole');
            $route->post('/updateRole', '/Permission/Permission/updateRole');
            $route->post('/delRole', '/Permission/Permission/delRole');
            $route->post('/updateRoleMenu', '/Permission/Permission/updateRoleMenu');
            $route->get('/getRoleList', '/Permission/Permission/getRoleList');
            $route->get('/getRoleInfo', '/Permission/Permission/getRoleInfo');
            //菜单
            $route->get('/menu/getMenuTree', '/Permission/Menu/getMenuTree');
            $route->get('/menu/getMenuAll', '/Permission/Menu/getMenuAll');//所有菜单迁移使用
            $route->get('/menu/getMenuById', '/Permission/Menu/getMenuById');
            $route->get('/menu/getMenuByParentId', '/Permission/Menu/getMenuByParentId');
            $route->post('/menu/add', '/Permission/Menu/addMenu');
            $route->post('/menu/update', '/Permission/Menu/updateMenu');
            $route->post('/menu/del', '/Permission/Menu/delMenu');
            $route->get('/menu/getMenuList', '/Permission/Menu/getMenuList');
            $route->get('/menu/getMenuCount', '/Permission/Menu/getMenuCount');
            $route->get('/menu/getSubMenu', '/Permission/Menu/getSubMenu');
            //管理 角色
            $route->get('/manager/getManagerList', '/Permission/Manage/getManagerList');//所有管理员迁移使用
            $route->get('/group/groupListAll', '/permission/Manage/groupListAll');//所有角色迁移使用
            $route->get('/group/groupList', '/permission/Manage/groupList');
            $route->get('/manager/getManagerInfo', '/Permission/Manage/getManagerInfo');
            $route->post('/manager/addZdmisMember', '/Permission/Manage/addZdmisMember');
            $route->post('/manager/updateZdmisMember', '/Permission/Manage/updateZdmisMember');
            $route->post('/manager/delZdmisMember', '/Permission/Manage/delZdmisMember');
            $route->post('/manager/addMemberFromZdmis', '/Permission/Manage/addMemberFromZdmis');
            $route->post('/manager/updateOrgSale', '/Permission/Manage/updateOrgSale'); //更新销售树
            $route->post('/manager/updateMemberRole', '/Permission/Manage/updateMemberRole');
            $route->get('/manager/getManagerCount', '/Permission/Manage/getManagerCount');
        });
        $route->post('/user/goods/del', '/Goods/UserGoods/delUserGoods');

        //订单相关接口
        $route->addGroup('/order', function (RouteCollector $route) {
            $route->post('/splitorder', '/Order/Order/splitOrder');
        });
        $route->addGroup('/order/modify', function (RouteCollector $route) {
            $route->post('/modifyPrice', '/Order/Modify/OrderModify/modifyPrice');
        });
    }
}