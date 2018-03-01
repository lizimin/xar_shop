import Mock from "mockjs";
// import { param2Obj } from '@/utils';

export default {
    /**
     * 识别车牌
     * Plate/ocrPlate
      {
       pathmd5:''  上传图片后的返回参数
      }
     */
    ocrPlate: config => {
        return Mock.mock({
            error: 0,
            mock: true,
            data: {
                plate_number: "@cword()", // 完整车牌信息
                car_plate: {
                    "car_plate_prefix1|1": ["云", "贵", "川"],
                    car_plate_prefix2: /[A-Z]{1}/,
                    car_plate_no: /[a-z][A-Z][0-9]{6}/
                }
            }
        });
    },
    /**
     * 识别是否是新老用户  如果返回结果为空 则为新用户
     * Plate/getCustomerByPlate
      {
       plate_number:''  //完整车牌号
      }
     */
    getCustomerByPlate: config => {
        return Mock.mock({
            error: 0,
            mock: true,
            data: {
                plate_number: "@cword()", // 完整车牌信息
                car_info: {
                    car_id: "@id",
                    cate_id: "@id",
                    brand_id: "@id",
                    car_name: "@id",
                    "car_minprice|1000-2000": 1,
                    "car_maxprice|2000-4000": 1,
                    car_img: Mock.Random.dataImage("140x100")
                },
                car_brand: {
                    brand_id: "@id",
                    brand_name: "@cword",
                },
                cate_list: [{
                        cate_id: "@id",
                        cate_name: "@name"
                    },
                    {
                        cate_id: "@id",
                        cate_name: "@name"
                    },
                    {
                        cate_id: "@id",
                        cate_name: "@name"
                    }
                ],
                costumer_info: [{
                    customer_name: "", // 用户信息
                    customer_sex: "", // 用户性别
                    customer_tel: "", // 用户电话
                    customer_id: "", // 用户地址
                    shop_id: "", //店铺id
                }]
            }
        });
    },
    /**
     *获取车辆品牌
     *Brand/getBrand
     * 已有正式接口
     */

    /**
     *获取车系
     *Brand/getBrandCar
     * 已有正式接口
     * 获取品牌下的车型
     {
        brand_id:''   //品牌id
     }
     */
    getBrandCar: config => {
        return {
            error: 0,
            data: Mock.mock({
                brand_id: "@id", //  品牌id
                cate_id: "@id", //   分类id
                cate_level: 1, // 当前分类的所属层级
                cate_name: "@id", //  分类名称
                cate_img: Mock.Random.dataImage("140x100"), //  分类图片
                cate_dep: "@cword", //  分类描述
                car_info: [],
                children: [{
                    brand_id: "@id", //  品牌id
                    cate_id: "@id", //   分类id
                    cate_level: 2, // 当前分类的所属层级
                    cate_name: "@id", //  分类名称
                    cate_img: Mock.Random.dataImage("140x100"), //  分类图片
                    cate_dep: "@cword", //  分类描述}
                    car_info: [],
                    children: [{
                        brand_id: "@id", //  品牌id
                        cate_id: "@id", //   分类id
                        cate_level: 3, // 当前分类的所属层级
                        cate_name: "@id", //  分类名称
                        cate_img: Mock.Random.dataImage("140x100"), //  分类图片
                        cate_dep: "@cword" //  分类描述}
                        car_info: [{
                            car_id: brand_id: cate_id: car_name: //"2018款 30周年版 30 TFSI 进取版"
                                car_price: //"29.28万"
                                maxprice: minprice:
                        }],
                    }]
                }]
            })
        };
    },
    /**
     * 获取检查点列表
     * CheckPoint/getCheckPoint
      {
       check_type:''  //0是功能   1是外观
       shop_id:''  //店铺ID
      }
     */
    getCheckPointInside: config => {
        // TODO   修改返回数据的构造
        return Mock.mock({
            error: 0,
            "data|9": [{
                chk_id: "@id",
                chk_dep: "@cword",
                "chk_type|+1": 1,
                "check_name|+1": [
                    "升降",
                    "中央",
                    "后视",
                    "雨刮",
                    "喇叭",
                    "点烟",
                    "音响",
                    "天线",
                    "天窗",
                    "空调"
                ],
                status: 0,
                imgSrc: "",
                detailOption: [{
                        typeId: 1,
                        typeName: "刮痕",
                        status: 0
                    },
                    {
                        typeId: 2,
                        typeName: "擦伤",
                        status: 0
                    }
                ]
            }]
        });
    },
    getCheckPointOut: config => {
        return {
            error: 0,
            data: Mock.mock({
                "check_list|5": [{
                    "positionType|+1": 1,
                    "positionName|+1": ["右侧", "车头", "顶部", "尾部", "左侧"],
                    status: 0,
                    imgSrc: "",
                    detailOption: [{
                            typeId: 1,
                            typeName: "刮痕",
                            status: 0
                        },
                        {
                            typeId: 2,
                            typeName: "擦伤",
                            status: 0
                        }
                    ]
                }]
            })
        };
    },

    /**
   * 保存车辆检查信息
   * ShopOrderRecp/addOrderRecp
  {
    car_plateno:String,          // 必填  TODO  verify
    car_mileage:String,          //里程
    car_oil:Int,                 //油量 默认为0
    car_brand:Int,               //  verify
    car_cate:Int,                // verify
    car_type:Int,                // 小型车  越野车  暂时可为空
    car_outside_remarks:String,  //
    car_inside_remarks:String,
	
	car_id:@id   //通过车辆型号选择获取
	customer_name：String,    // 客户姓名
	customer_sex:Int,  // 客户性别  0是女  1 是男
	customer_tel:String,  
	customer_id:Int,
	
    car_outside_check:[
      {
        chk_id:Int,
        chkorder_point:String,    //搞个分割符   把点选的问题功能点 字符串拼起来['刮痕|损坏|变形']
        chkorder_img:String,      //上图图片之后返回的path字段
        chkorder_remarks:String,  //暂时为空
      }
    ],
    car_inside_check:[
      {
        chk_id:Int,
        chkorder_point:String,//搞个分割符   把点选的问题功能点 字符串拼起来
        chkorder_img:String,
        chkorder_remarks:String,//暂时为空
      }
    ],
  }
   */
    addOrderRecp: config => {
        return {
            error: 0,
            data: Mock.mock({
                order_sn: "@id", //  接车单随机key,(用于获取对应订单)(用于获取确认二维码)(用于获取车辆检查信息列表)
                job_sn: "@id", //  派工单随机key,(用于获取对应派工单)(用于获取确认二维码)
                car_id: "@id", //  关联的车辆检查信息，可以通过id获取  车辆信息（品牌 车牌号）
                carorder_id: "@id",
                customer_id: "@id",
                customer_name: "@cname",
                customer_tel: /\d{11}/
            })
        };
    },
    /**
     * 获取车辆检查信息列表
     * ShopOrderRecp/getOrderRecpInfo
    {
      //  TODO---mockjs
      //  TODO---mockjs
      order_sn:String,                 //  接车单随机key,(用于获取对应订单)(用于获取车辆检查信息列表)
    }
     */
    getOrderRecpInfo: config => {
        //  返回  ShopOrderRecp/addOrderRecp  接口插入成功的值  参考上一个接口的传入参数

        return Mock.mock({
            error: 0
        });
    },
    /**
 * 获取服务接口
 * Service/getshopService
{
  //  TODO---
  shop_id:Int,        //店铺ID  全局store中获取
}
 */
    getshopService: config => {
        return Mock.mock({
            error: 0,
            data: {
                "service_group|14": [{
                    sg_id: "@id", // 服务组id
                    sg_name: "@cword(3)", // 服务组名称
                    "service|1-10": [{
                        service_id: "@id", // 服务项id
                        service_price: "@integer(100,1000)", // 参考价格
                        service_name: "@cword(4)", // 服务名称
                        "service_type|1": [0, 1], // 服务类型 0是标准服务产品  1是多产品服务组合
                        service_dep: "@cword(5)", // 服务描述
                        service_img: "@cword(5)" // 服务图片
                    }]
                }]
            }
        });
    },

    /**
   * 提交服务项
   * ShopOrderJob/addOrderJob
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
    addOrderJob: config => {
        return {
            error: 0,
            data: Mock.mock({
                job_sn: "@id", //  派工单sn  用于获取确认二维码
                job_id: "@id" //   派工单id  用于获取确认二维码
            })
        };
    },
    /**
     * 获取已提交的服务项
     * ShopOrderJob/getOrderJobInfo
    {
      job_sn: String, //派工单ID
    }
     */
    getOrderJobInfo: config => {
        // TODO  mockjs
        // 参考上一个接口提交的数据
        return {
            error: 0,
            data: Mock.mock({})
        };
    },
    /**
    * 获取店铺工种列表
    * ShopUserGroup/getShopUserGroup
    {
    shop_id:Int,        //店铺ID  全局store中获取
    }
    */
    getShopUserGroup: config => {
        return Mock.mock({
            error: 0,
            data: [{
                group_id: "@id", // 店铺工种组id
                group_name: "@cword(3)", // 店铺工种组名称
                shop_user_id: "@id", // 组长id
                shop_user_name: "@cname" // 组长姓名
            }]
        });
    },
    /**
     * 提交服务项成功后
     * ShopOrderRecp/updateOrderRecp
    {
      carorder_id:int,//ShopOrderRecp/addOrderRecp中已获取
      order_sn:String,//ShopOrderRecp/addOrderRecp中已获取
      order_insurance:String,  //保险公司名字
      order_deadline:Int,  //时间戳
      order_debit:Int,  //发票状态   0   1
      order_identity_customer:String,  //身份证暂存   0   1
      order_identity_car:String,  //行车证暂存   0   1
      order_remarks_customer:String,  //用户备注  0   1
      order_discount:String,  //备注  0   1
      order_allprice_discount:String,  //备注  0   1
    }
     */
    updateOrderRecp: config => {
        return {
            error: 0,
            data: Mock.mock({
                job_sn: "@id" //  工单id  用于获取确认二维码
            })
        };
    },
    /**
     * 获取订单确认二维码
     * ShopOrderRecp/getQRcodeByJobsn
     {
       job_sn:String,  //提交服务项已返回
     }
     */
    getQRcodeByJobsn: config => {
        return {
            error: 0,
            data: Mock.mock({
                qrcode_link: "@url",
                qrcode_overtime: "@integer(10, 20)" //  秒数
            })
        };
    },
    /**
     * 提交选择服务项
     * ShopOrderRecp/getOrderRecpList
    {
      service_list:[{
        job_sn:String,
        order_sn:String,
        job_remarks:String,
        car_id:Int,//上一步中返回的
        card_plateno:String,
        service_id:Int,
        service_name:String,
        service_price:String,
        customer_id:Int,
      }]
    }
     */
    getOrderRecpList: config => {
        return {
            error: 0,
            data: Mock.mock({
                job_sn: "@id" // 工单id  用于获取确认二维码
            })
        };
    }


};