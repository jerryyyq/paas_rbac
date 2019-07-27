# PAAS 平台通用 RBAC 管理系统
本管理系统的目标是：针对 PAAS 平台的资源、权限、角色管理给出统一的解决方案。  
server 目录下是服务器端 PHP 代码与数据库建库脚本  
当你 clone 后，务必到工程目录执行：$ composer install

## 平台结构说明
```
平台 ┬─ 企业一 ┬─ web 站点一
　　 |  　　　 ├- web 站点二
　　 │  　　　 │  ...
　　 │  　　　 └─ web 站点 N
　　 │
　　 ├- 企业二 ┬─ web 站点一
　　 |  　　　 ├- web 站点二
　　 │  　　　 │  ...
　　 │  　　　 └─ web 站点 N
　　 │  ...
　　 │
　　 └- 企业 N ┬─ web 站点一
　　    　　   ├- web 站点二
　　    　　   │  ...
　　    　　   └─ web 站点 N
```
系统管理站点、企业管理站点和终端用户站点应分开，所以要分为三套独立的前后端程序。
系统采用统一的用户表，一个用户登录这三类站点时，这三类站点的后台分别去检查相应的权限。
所有网站的资源与角色模型是一样的。

## 概念描述
* 资源: 可以被访问的对象，系统、企业、网站等（或将来其他的数据）。一个资源是否可以被访问或以何种方法被访问，由权限来控制。资源并没有独立的表。在其他表中对资源的引用通过两个字段：resource_type 和 id_resource。目前 resource_type 有三个可选值：0:sys, 1:enteprise, 2:websit；根据不同的 resource_type 去对应不同的具体 id_resource。例如 当 resource_type == 1 时，id_resource 的值就是 id_enteprise。

* 权限：是最小权利描述项，权限可以有父子关系，权限将会控制资源被访问的方式。
* 角色：是一组权限的权限包，方便为用户赋权。角色不包含资源与权限的关联关系。
* 用户：同一个用户既可以是某个企业的超级管理员，也可以是另一个企业的日志管理员，所有的控制都是由“用户-资源-角色表”描述的。
* 用户-资源-角色表：最核心的为用户赋权的数据库表。

## 数据模型
### ac_privilege
权限表，记录全系统内所有的权限，例如：修改、增加、读取、删除、执行。权限支持多级权限。
* id_privilege 主键
* id_father 父权限的 id_privilege 值。如果该值为 0，表示本身是顶级权限
* have_child 是否有子权限, 用于支持多层级权限系统。如果该值为 0，表示没有子权限。
* name 标识名，用在系统内进行权限判断时使用
* show_name 显示名，方便阅读

### ac_rule
角色表，记录系统内所有的角色，这些角色分三类：系统的，企业的，web 站点的
* id_rule 主键
* resource_type 保留使用，可以忽略。0:sys, 1:enteprise, 2:websit ...
* name 标识名，用在系统内进行权限判断时使用
* show_name 显示名，方便阅读
* description 附加描述

### ac_rule_privilege
角色、权限 关联表。一个角色可以关联多个权限
* id
* id_rule
* id_privilege
* description 附加描述

### ac_user_resource_rule
用户、资源、角色 关联表。一个用户可以关联多个资源的多个角色。
* id
* id_user
* id_rule
* resource_type 保留使用，可以忽略。
* id_resource
* description 附加描述

### ac_user
* id_user
* name 用户名
* email
* mobile
* salt
* password
* real_name
* state 用户的状态：例如是否激活、注销等等
* id_channel 是从哪个渠道加过来的
* oauth_platform_type 第三方登录平台类型
* wx_unionid
* wx_openid
* qq_openid
* sina_openid
* token 用于跨站点统一登录，无此需求可以忽略
* token_create_time token 创建时间
* registe_date 注册时间
* email_verify_state
* email_verify_code
* email_verify_code_send_time
* mobile_verify_state
* mobile_verify_code
* mobile_verify_code_send_time

### ac_enterprise
企业表
* id_enterprise 主键
* symbol_name 在整个系统中的唯一符号名，用于企业管理员登录时标明自己隶属于哪个企业
* real_name 真实名称
* country
* province
* address
* zipcode
* description
* state 状态，默认为 0
* registe_date 登记日期

### ac_website
* id_website 主键：网站 id
* id_enterprise 归属的企业 id
* name 网站名
* url 网站 url
* symbol_name 在整个系统中的唯一符号名，
* style 网站风格（皮肤和式样），默认为 0
* description
* state 状态，默认为 0
* registe_date 登记日期



## 入参与出参
* 所有的入参都封装到 json 串中，GET 为 a 参数，POST 时直接为 body，也可以为 a 参数
    * 统一的 URL 为 https://www.xxxxx.com/pass_rbac.php?m=some_api_name&a={"a":aaa,"b":bbb}
* 所有的应答返回值都封装到 json 串中，{"err":0, "err_msg":"", "data":{}}
    * err 为应答码，0 表示成功，其它值表示失败。应用错误码从 -100 开始。
    * err_msg 为具体错误信息
    * data 为返回的数据，具体名称由各自的数据决定
* 系统错误码
| 错误码 | 含义 |
| ---- | ---- |
| 0 | 成功 |
| -1 | 参数错误 |
| -2 | 没有相应权限 |

## 系统登录入口
PAAS 系统，应该有三个登录入口
1. 系统管理员登录入口
* paas_sys.php?m=login&a={user:xxxx,password:xxxx}
2. 企业管理员登录入口
* paas_enterprise.php?m=login&a={enterprise:企业符号名,user:xxxx,password:xxxx}
3. 最终用户登录入口
* paas_web.php?m=login&a={enterprise:网站符号名,user:xxxx,password:xxxx}
* 此处跟商业模式有关，可能的模式有两类，一类是在餐厅点餐模式：用户直接登录本餐厅，进行点餐等活动。一类是外卖模式：用户可以查看所有企业的商店，进行点餐等活动。
4. 出参：{ "err":0, "err_msg":"", "user_info":{}, "user_privilege":['resource_privilege', 'privileges'] }

## 系统管理员
系统管理员用于管理整个系统，他们可以按 "系统 RBAC" 进行分权。
系统管理员可以创建或删除企业账户。
系统管理员存储在表：sys_admin。
系统管理员登录帐号必须全局唯一

## 企业管理员
企业管理员用于管理本企业的各个 web 站点，他们可以按 "企业 RBAC" 进行分权。
企业管理员可以创建或删除本企业的 web 站点，管理本企业的各个 web 站点的最终用户。
企业管理员存储在表：enterprise_admin。
企业管理员登录帐号必须全局唯一



# API
## sys_admin_login( args = ['email', 'password'] )
* 入参 args = ['email', 'password']
* 出参 user_info，内容为：['id_admin', 'name', 'type', ...]
    * 其中 type 为：0:sys, 1:enteprise, 2:websit
* 出参 user_privilege，内容为：['resource_privilege', 'privileges']
    * 其中 resource_privilege 为：资源权限数组； privileges 为权限数组。


# minimum_frame
这是一套由我开发的极简 PHP 后台 API 框架，以 json 作为输入输出参数格式。
这套框架包含了极简的路由系统；封装了 mysql，memcache，log 三大基础组件。
以 yyq_frame_main 做为主入口函数，包含了 API 名检查、API 参数检查、以及标准的错误输出。



# 附录：常用制表符
```
┌┬┐  ┌─┐  ┏┳┓  ┏━┓  ┎┰┒  ┍┯┑
├┼┤  │┼│  ┣╋┫  ┃╋┃  ┠╂┨  ┝┿┥
└┴┘  └─┘  ┗┻┛  ┗━┛  ┖┸┚  ┕┷┙

╔╦╗  ╔═╗  ╓╥╖  ╒╤╕
╠╬╣  ║╬║  ╟╫╢  ╞╪╡
╚╩╝  ╚═╝  ╙╨╜  ╘╧╛

┡  ┢  ┱  ┲  ┩ ┧ ┹  ┺  

╃  ╄  ╆  ╅

┽  ╀  ┾  ╁

╊  ╈  ╉  ╇

╭╮  ╲╱
╰╯  ╱╲

╭─╮
│╳│
╰─╯
```


Author: 杨玉奇
email: yangyuqi@sina.com
url: https://github.com/jerryyyq/tf_algorithm_example
copyright yangyuqi
著作权归作者 杨玉奇 所有。商业转载请联系作者获得授权，非商业转载请注明出处。
date: 2017-09-22
