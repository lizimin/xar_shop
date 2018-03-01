import request from "@/utils/request";
const baseUrl = "https://xcxcgj.gotomore.cn/api/api.php?s=";
export default class {
  /**
   * @api {post} /ShopUser/userInfo userInfo
   * @apiName userInfo
   * @apiGroup  Common
   * @apiVersion 0.0.1
   * @apiDescription 用户信息
   * @apiParamExample  {type} Request-Example:
    data:{
      user: {
        token: "@increment",
        unionid: "@id",
        shop_user_id: "@id", // 接待员用户id
        uname: "@cname", // 接待员用户名
        urealname: "@cname", // 接待员真实姓名
        group_id: "@id", //  接待组id
        shop_id: "@id", // 店铺id
        utel: /1\d{11}/ // 接待员手机号
      },
      user_shop: {
        shop_id: "@id",
        shop_name: "@county(true)",
        shop_tel: "/d{5,10}-/",
        shop_lat: "@ip", //  经纬度
        shop_lng: "@ip", //  经纬度
        shop_group_id: "@id" //  所属集团id
      },
      user_role: [
        {
          role_id: "@id", // 角色ID
          role_name: "@cname", // 角色名称,
          "role_discount|0-100": 1 // 折扣  取范围 0-100
        }
      ], //
      discount_list: [
        {
          discount_name: "@cword(3)",
          "discount_value|1-100": 1
        }
      ],
      user_group: {
        group_id: "@id", //  组id
        shop_user_id: "@id", //  组长用户id
        "group_name|1": ["接待1组", "接待2组"] //  组名称 例如 漆工1组
      }
    }
   *
   */
  static async userInfo(data) {
    const url = `${baseUrl}/Plate/ocrPlate`;
    return await request.post(url, data);
  }
  /**
   * @api {post} /Plate/orcPlate orcPlate
   * @apiName orcPlate1
   * @apiGroup  CarInfo
   * @apiDescription 车牌识别
   * @apiParam  {String}  pathmd5  图片上传后返回值
   * @apiParamExample  {type} Request-Example:
   {
       pathmd5 : {String}  // 上传图片后的返回参数
   }
    * @apiSuccessExample {type} Success-Response:
   {
       plate_number : 云A987NE  // {String}云A987NE
       car_plate:{
         car_plate_prefix1: 云
         car_plate_prefix1: A
         car_plate_prefix2: 987NE
       }
   }
  *
   */
  static async ocrPlate(data) {
    const url = `${baseUrl}/Plate/ocrPlate`;
    return await request.post(url, data);
  }

  /**
   * @api {post}  /Plate/getCustomerByPlate getCustomerByPlate
   * @apiName getCustomerByPlate
   * @apiGroup  CarInfo
   * @apiDescription 通过车牌获取相关用户信息
   * @apiParam {String} plate_number
    {
       plate_number : 云A987NE //  完整车牌号
   }
    * @apiSuccessExample {type} Success-Response:
   {
       plate_number : {String} 云A987NE
       car_brand:{
         brand_id:  1
         brand_name: 广汽
         cate_list: [
           {
              cate_id: "@id",
              cate_name: "本田"
           },{
              cate_id: "@id",
              cate_name: "飞度"
           },{
              cate_id: "@id",
              cate_name: "2018款"
           }
        ]
       }
   }
   */
  static async getCustomerByPlate(data) {
    const url = `${baseUrl}/Plate/ocrPlate`;
    return await request.post(url, data);
  }

  /**
   * @api {post}  /Brand/getBrand getBrand
   * @apiName getBrand
   * @apiGroup  CarBrand
   * @apiDescription 获取车辆品牌信息
   */
  static async getBrand(data) {
    const url = `${baseUrl}/Brand/getBrand`;
    return await request.post(url, data);
  }

  /**
   * @api {post} /Brand/getBrandCar getBrandCar
   * @apiName getBrandCar
   * @apiGroup  CarBrand
   * @apiDescription 获取某个车辆品牌下的三级列表
   * @apiParam {Int} brand_id
   */
  static async getBrandCar(data) {
    // console.log(data);
    const url = `${baseUrl}/Brand/getBrandCar`;
    return await request.post(url, data);
  }

  /**
   * @api {post} /CheckPoint/getCheckPoint getCheckPiont
   * @apiName getCheckPiont
   * @apiGroup  CarInfo
   * @apiDescription  获取检查点列表
   * @apiParam {Int}  type   0为功能列表   1为外观列表
   */
  static async getCheckPoint(data) {
    // console.log(data, 0);
    if (data.type === 1) {
      const url = `${baseUrl}/CheckPoint/getCheckPointOut`;
      return await request.post(url, data);
    } else {
      const url = `${baseUrl}/CheckPoint/getCheckPointInside`;
      return await request.post(url, data);
    }
  }

  /**
   * @api {post} /ShopOrderRecp/addOrderRecp addOrderRecp
   * @apiName addOrderRecp
   * @apiGroup CarInfo
   * @apiDescription 添加外观检查 同时生成一个接车单和一个派工单 参考recp.js
   * @apiParam {Object}   recp_info 参数参考repc.js
   */
  static async addOrderRecp(data) {
    const url = `${baseUrl}/ShopOrderRecp/addOrderRecp`;
    return await request.post(url, data);
  }

  /**
   * @api {post} /ShopOrderRecp/getOrderRecpInfo getOrderRecpInfo
   * @apiName getOrderRecpInfo
   * @apiGroup CarInfo
   * @apiDescription 获取接车单详细信息
   * @apiParam {Int}   order_sn 参数参考repc.js
   * @apiSuccessExample {type} Success-Response:
     {
         //----------基础字段参考 getOrderRecpList  以下为订单折扣申请日志信息
         discount_loglist:[{
           discount_status:Int, // 0  为审批中  1  为审批结束  已通过   -1 为审批结束  未通过
           discount_createtime:Int , // 时间戳
           discount_endtime:Int , // 时间戳
           discount_tips:Int , // 当前状态下的 文字提示   比如 xxx审批中  未通过审批 原因为XXX
         }]
     }
   *   */
  static async getOrderRecpInfo(data) {
    const url = `${baseUrl}/ShopOrderRecp/getOrderRecpInfo`;
    return await request.post(url, data);
  }

  /**
   * @api {post} /Service/getShopService getShopService
   * @apiName getShopService
   * @apiGroup CustomerShop
   * @apiDescription 获取店铺的服务列表
   * @apiParam {Int}   shop_id 参数参考repc.js
   */
  static async getShopService(data) {
    const url = `${baseUrl}/Service/getshopService`;
    return await request.post(url, data);
  }
  /**
   * @api {post} /ShopOrderJob/addOrderCustomerJob addOrderCustomerJob
   * @apiName addOrderCustomerJob
   * @apiGroup CustomerShop
   * @apiDescription 提交所有与客户确认时选择的服务项
   * @apiParam {Object}   job_info 参数参考repc.js
   * @apiParamExample  {type} Request-Example:
     {
      job_sn: String, //派工单ID
      order_sn: String, //接车单ID

      car_id: Int, //store
      material_list_price: Float, //材料总价
      service_list_price: Float, //工时总价
      all_price: Float, //以上2项总价
      card_plateno: String,
      service_list: [
      {
        sg_id: Int, // 服务组ID
        service_id: Int, //
        service_name: String, //
        service_price: String, //  可以手动输入
        customer_id: Int, //
        work_group_id: Int, //店铺工种组id
        job_remarks: String, //接车单备注
      }
      ],
      material_list: [{
        sg_id: Int, // 服务组ID
        service_name: String,    //
        service_price: String,   //
        server_count: Int,      //材料数量
        server_totalprice: Int,     //材料总价
        customer_id: Int,         //
        work_group_id: Int, //店铺工种组id
        job_remarks: String, //接车单备注
      }]
    }
   */
  static async addOrderCustomerJob(data) {
    const url = `${baseUrl}/ShopOrderJob/addOrderJob`;
    return await request.post(url, data);
  }

  /**
   * @api {post} /ShopOrderRecp/updateOrderRecp updateOrderRecp
   * @apiName updateOrderRecp
   * @apiGroup OrderSupplement
   * @apiDescription 更新接车单信息  用于更新用户身份证信息等，折扣信息需要单独接口更新
   * @apiParam {Int}   job_sn 添加服务列表时获取的job_sn
   * @apiParamExample  {type} Request-Example:
    {
      carorder_id:int,//ShopOrderRecp/addOrderRecp中已获取
      order_sn:String,//ShopOrderRecp/addOrderRecp中已获取
      order_insurance:String,  //保险公司名字
      order_deadline:Int,  //时间戳
      order_debit:Int,  //发票状态   0   1
      order_identity_customer:String,  //身份证暂存   0   1
      order_identity_car:String,  //行车证暂存   0   1
      order_remarks_customer:String,  //用户备注  0   1
      order_discount:Int,  //备注  0 -100
      order_allprice_discount:Int,  //  备注
    }
   */
  static async updateOrderRecp(data) {
    const url = `${baseUrl}/ShopOrderRecp/updateOrderRecp`;
    return await request.post(url, data);
  }

  /**
   *
   * @api {post} /ShopOrderRecp/getOrderRecpList getOrderRecpList
   * @apiName getOrderRecpList
   * @apiGroup RecpList
   * @apiVersion 0.0.1
   * @apiDescription 获取接车单列表
   *
   * @apiParam  {String} type 接车单状态类型
   *
   *
   * @apiParamExample  {type} Request-Example:
     {
        order_status:1    // 接车单状态类型  0为全部  -1为作废   1为未派 2为已派
        page :Int,
        page_size :Int,
     }
   *
   *
   * @apiSuccessExample {type} Success-Response:
     {
      data:[{
      job_sn:String,
      order_sn:String,  //  接车单的订单key
      carorder_id:String,  //  接车单的订单号
      carorder_remarks:String,  // 接车单备注
      car_id:Int,//上一步中返回的
      card_plateno:String,
      service_id:Int,
      service_list:[{
        service_name:String,  // 服务名称
      }],
      pay_price:Int,      //  待支付价格
      all_price:Int,      // 打折前总价
      order_discount:Int,      // 折扣
      order_allprice_discount:Int,      // 折扣后总价
      customer_id:Int,
      create_time:Int,          //  格式为时间戳   接车时间
      order_deadline:Int,          //  格式为时间戳   预计提车时间
      order_status:Int ,   //接车单状态类型  0为全部  -1为作废   1为未派 2为已派
      chilren:[
        {
          job_sn:String,
          order_sn:String,  //  接车单的订单key
          carorder_id:String,  //  接车单的订单号
          carorder_remarks:String,  // 接车单备注
          car_id:Int,//上一步中返回的
          card_plateno:String,
          service_id:Int,
          service_list:[{
            service_name:String,  // 服务名称
          }],
          pay_price:Int,      //  待支付价格
          all_price:Int,      // 打折前总价
          order_discount:Int,      // 折扣
          order_allprice_discount:Int,      // 折扣后总价
          customer_id:Int,
          create_time:Int,          //  格式为时间戳   接车时间
          order_deadline:Int,          //  格式为时间戳   预计提车时间
          order_status:Int ,   //接车单状态类型  0为全部  -1为作废   1为未派 2为已派
        }
      ]
      }]
    }
   *
   *
   */
  static async getOrderRecpList() {}

  /**
   *
   * @api {post} /ShopOrderJob/addSubOrderJob  addSubOrderJob
   * @apiName addSubOrderJob
   * @apiGroup addSubOrderJob
   * @apiVersion  0.0.1
   * @apiDescription 添加子订单
   * @apiParam  {String} order_psn 父级订单的sn
   * @apiParamExample  {type} Request-Example:
     {
      order_psn:String,  //父级订单的sn
      material_list_price: Float, //材料总价
      service_list_price: Float, //工时总价
      all_price: Float, //以上2项总价
      service_list: [
      {
        sg_id: Int, // 服务组ID
        service_id: Int, //
        service_name: String, //
        service_price: String, //  可以手动输入
        customer_id: Int, //
        work_group_id: Int, //店铺工种组id
        job_remarks: String, //接车单备注
      }
      ],
      material_list: [{
        sg_id: Int, // 服务组ID
        service_name: String,    //
        service_price: String,   //
        server_count: Int,      //材料数量
        server_totalprice: Int,     //材料总价
        customer_id: Int,         //
        work_group_id: Int, //店铺工种组id
        job_remarks: String, //接车单备注
      }],
      //----------------微信通知模块
      wechat_notice:{
        wx_order_price:Int, // 手动输入价格
        wx_title:String,
        wx_remarks:String,
        wx_content:String,
        wx_image:String

      }
    }
   *
   */
  static async addSubOrderJob() {}

  /**
   *
   * @api {post} /ShopOrderRecp/changeOrderStatus  changeOrderStatus
   * @apiName changeOrderStatus
   * @apiGroup RecpList
   * @apiVersion  0.0.1
   * @apiDescription 更新接车单状态目前默认为只可将未派单的接车单作废
   * @apiParam  {String} order_pid 父级订单的sn
   * @apiParamExample  {type} Request-Example:
     {
      order_sn: String, //接车单ID
      status_type: Int, //接车单状态类型   默认不传 预留

    }
   *
   */

  static async changeOrderStatus() {}

  /**
   *
   * @api {post} /ShopOrder/applyOrderDiscount applyOrderDiscount
   * @apiName applyOrderDiscount
   * @apiGroup applyOrderDiscount
   * @apiDescription 发起一个申请打折的流程
   * @apiVersion  0.0.1
   *
   *
   * @apiParam  {String} paramName description
   *
   * @apiSuccess (200) {type} name description
   *
   * @apiParamExample  {type} Request-Example:
     {
         order_sn : Sting , //订单key
         discount : Int , //0-100
     }
   *
   *
   * @apiSuccessExample {type} Success-Response:
     {
         property : value
     }
   *
   *
   */
  static async applyOrderDiscount(data) {}

  /**
   * @api {post} /ShopOrderJob/getOrderJobInfo getOrderJobInfo
   * @apiName WorkerShop
   * @apiGroup WorkerShop
   * @apiDescription 获取提交的服务和材料列表
   * @apiParam {Int}   job_sn 添加服务列表时获取的job_sn
   * @apiParamExample  {type} Request-Example:
     {
      sg_id: "@id", // 服务组id
      sg_name: "@cword(3)", // 服务组名称
      job_remarks: "@cword(20)", // 服务组名称
      work_group_id:'@id', //所选工组ID
      "service_list|1-10": [
        {
          service_id: "@id", // 服务项id
          service_price: "@integer(100,1000)", // 参考价格
          service_name: "@cword(4)", // 服务名称
          service_count: Int, // 数量
          service_totalprice: Int, // 总价
          "service_type|1": [0, 1], // 服务类型 0是标准服务产品  1是多产品服务组合
          service_dep: "@cword(5)", // 服务描述
          service_img: "@cword(5)" // 服务图片
        }
      ],
      "material_list|1-10": [
        {
          service_id: "@id", // 材料项id
          service_price: "@integer(100,1000)", // 参考价格
          service_name: "@cword(4)", // 材料名称
          service_count: Int, // 数量
          service_totalprice: Int, // 总价
          "service_type|1": [0, 1], // 材料类型 0是标准材料产品  1是多产品材料组合
          service_dep: "@cword(5)", // 材料描述
          service_img: "@cword(5)" // 材料图片
        }
      ]
      }
    *
   */
  static async getOrderJobInfo(data) {
    const url = `${baseUrl}/ShopOrderJob/getOrderJobInfo`;
    return await request.post(url, data);
  }
  
   /**
   * @api {post} /ShopJob/getOrderWorkerJob getOrderWorkerJob
   * @apiName WorkerShop
   * @apiGroup WorkerShop
   * @apiDescription 获取提交的服务和材料列表
   * @apiParam {Int}   job_sn 添加服务列表时获取的job_sn
   * @apiParamExample  {type} Request-Example:
     {
      sg_id: "@id", // 服务组id
      sg_name: "@cword(3)", // 服务组名称
      job_remarks: "@cword(20)", // 服务组名称
      work_group_id:'@id', //所选工组ID
      "service_list|1-10": [
        {
          service_id: "@id", // 服务项id
          service_price: "@integer(100,1000)", // 参考价格
          service_name: "@cword(4)", // 服务名称
          service_count: Int, // 数量
          service_totalprice: Int, // 总价
          "service_type|1": [0, 1], // 服务类型 0是标准服务产品  1是多产品服务组合
          service_dep: "@cword(5)", // 服务描述
          service_img: "@cword(5)" // 服务图片
        }
      ],
      "material_list|1-10": [
        {
          service_id: "@id", // 材料项id
          service_price: "@integer(100,1000)", // 参考价格
          service_name: "@cword(4)", // 材料名称
          service_count: Int, // 数量
          service_totalprice: Int, // 总价
          "service_type|1": [0, 1], // 材料类型 0是标准材料产品  1是多产品材料组合
          service_dep: "@cword(5)", // 材料描述
          service_img: "@cword(5)" // 材料图片
        }
      ]
      }
    *
   */
  static async getOrderWorkerJob(data) {
    const url = `${baseUrl}/ShopJob/getOrderWorkerJob`;
    return await request.post(url, data);
  }
  
  /**
   *
   * @api {post} /ShopJob/addOrderWorkerJob addOrderWorkerJob
   * @apiName addOrderWorkerJob
   * @apiGroup WorkerShop
   * @apiDescription 派工单确认信息
   * @apiParam {Int}   job_sn 添加服务列表时获取的job_sn
   * @apiParamExample  {type} Request-Example:
     {
      job_sn: String, //派工单ID
      order_sn: String, //接车单ID
	  
      car_id: Int, //store
      material_list_price: Float, //材料总价
      service_list_price: Float, //工时总价
      all_price: Float, //以上2项总价
      card_plateno: String,
      service_list: [
      {
        sg_id: Int, // 服务组ID
        service_id: Int, //
        service_name: String, //
        service_price: String, //  可以手动输入
        customer_id: Int, //
        work_group_id: Int, //店铺工种组id
        job_remarks: String, //接车单备注
      }
      ],
      material_list: [{
        sg_id: Int, // 服务组ID
        service_name: String,    //
        service_price: String,   //
        server_count: Int,      //材料数量
        server_totalprice: Int,     //材料总价
        customer_id: Int,         //
        work_group_id: Int, //店铺工种组id
        job_remarks: String, //接车单备注
      }]
      }
    *
   */
  static async addOrderWorkerJob(data) {
    const url = `${baseUrl}/ShopJob/addOrderWorkerJob`;
    return await request.post(url, data);
  }

  /**
   *
   * @api {post} /ShopOrder/getOrderProgress getOrderProgress
   * @apiName getOrderProgress
   * @apiGroup Progress
   * @apiDescription 获取订单进度信息
   *
   * @apiParam  {String} order_sn description
   *
   * @apiSuccess (200) {type} name description
   *
   * @apiParamExample  {type} Request-Example:
     {
         recp_info : {
           // 参照getOrderRecpInfo
           //  接车单信息和派工单单号都有了
         },
         job_list:[{
           worker_group_id:Int,
           worker_group_name:String,   //工组名称
           worker_user_name:String,      //负责人姓名
           worker_status:Int,      //0为工组长未确认  1是 工组长已认领派工单信息  2  是工组长已处理接受到的工单信息  3是接待员已确认
         }],
         pay_info:{
           pay_type:Int,    //0 是现金  1 是微信  2是支付宝
           pay_status:Int,   //0是未支付   1 是已支付
           pay_price:Int,   //支付金额
         },
         delivery_status:Int  //是否可以触发交车按钮
     }
   *
   */
  static async getOrderProgress(data) {
    const url = `${baseUrl}/ShopOrder/getOrderProgress`;
    return await request.post(url, data);
  }

  /**
   *
   * @api {post} /ShopOrder/addPayInfo addPayInfo
   * @apiName addPayInfo
   * @apiGroup Progress
   * @apiDescription 支付
   *
   * @apiParamExample  {type} Request-Example:
     {
       order_sn:String,  //订单号
       pay_type:Int,  //0 是现金  1 是微信  2是支付宝
       pay_image:String //单据凭证
       pya_bill_no:String //单据号
     }
   *
   */
  static async addPayInfo(data) {
    const url = `${baseUrl}/ShopOrderJob/getOrderJobInfo`;
    return await request.post(url, data);
  }
}

//  TOOD
// 添加车辆信息的接口需要添加vin码


// ---------------------------------------
//新增相关接口
  
  /**
   *
   * @api {post} /ShopOrder/getQRcodeByOrdersn getQRcodeByOrdersn
   * @apiName getQRcodeByOrdersn
   * @apiGroup ShopOrder
   * @apiDescription 获取接待订单二维码链接和过期时间
   *
   * @apiParamExample  {type} Request-Example:
     {
       order_sn:String,  //订单号
     }
   *
   */
   
   /**
   *
   * @api {post} /ShopOrder/confirmOrderRecp confirmOrderRecp
   * @apiName confirmOrderRecp
   * @apiGroup ShopOrder
   * @apiDescription 接待员对整个接车单进行确认交车
   *
   * @apiParamExample  {type} Request-Example:
     {
       order_sn:String,  //订单号
	   shop_user_id:String,  //接待人员用户id
	   work_group_id:int,  //所属组id
     }
   *
   */
   
   /**
   *
   * @api {post} /ShopJob/confirmWorkerJobGroupLeader confirmWorkerJobGroupLeader
   * @apiName confirmWorkerJobGroupLeader
   * @apiGroup WorkerShop
   * @apiDescription 组长确认工单信息修改状态
   *
   * @apiParamExample  {type} Request-Example:
     {
       job_sn:String,  //订单job_sn号
	   shop_user_id:int,  //组长用户id
	   work_group_id:int,  //组长所属组id
     }
   *
   */
   
   /**
   *
   * @api {post} /ShopJob/confirmWorkerJobRecp confirmWorkerJobRecp
   * @apiName confirmWorkerJobRecp
   * @apiGroup WorkerShop
   * @apiDescription 接待员对每个工种组进行确认
   *
   * @apiParamExample  {type} Request-Example:
     {
       job_sn:String,  //订单job_sn号
	   shop_user_id:int,  //组长用户id
	   work_group_id:int,  //组长所属组id
     }
   *
   */
   
   /**
   *
   * @api {post} /ShopOrder/approveOrderDiscount approveOrderDiscount
   * @apiName approveOrderDiscount
   * @apiGroup WorkerShop
   * @apiDescription 申请折扣审批
   *
   * @apiParamExample  {type} Request-Example:
     {
       log_id:String,  //申请折扣日志id
	   shop_user_id:int,  //审批人用户id
	   discount_status:int,  //审批折扣状态 1：审批通过，-1：审批拒绝
	   discount_tips:  //审批说明
     }
   *
   */
   
   





