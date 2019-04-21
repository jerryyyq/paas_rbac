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
所有网站的资源与角色模型是一样的，所以 ac_rule_resource_privilege 只有一个表，不区分网站。
只有不同网站的用户对应不同的权限组。
所以，每个网站有自己独立的：user_ 和 ac_user_rule_ 表。


## 数据模型
### ac_privilege
权限表，记录全系统内所有的权限，例如：修改、增加、读取、删除、执行。

权限支持也仅支持 2 级权限。
* id_privilege 主键
* id_father 父权限的 id_privilege 值。如果该值为 0，表示本身是顶级权限
* name 标识名，用在系统内进行权限判断时使用
* show_name 显示名，方便阅读

### ac_resource
资源表，记录系统内所有需要被权限隔离的资源，这些资源分三类：系统的，企业的，web 站点的
* id_resource 主键
* type 类型 0:sys, 1:enteprise, 2:websit ...
* relation_id 关联 id，可能是 管理员 id, 企业 id, websit id, 行为 id 等；也可能是 0，表示无关联项
* name 标识名，用在系统内进行权限判断时使用
* show_name 显示名，方便阅读

### ac_rule
角色表，记录系统内所有的角色，这些角色分三类：系统的，企业的，web 站点的
* id_rule 主键
* type 类型 0:sys, 1:enteprise, 2:websit ...
* name 标识名，用在系统内进行权限判断时使用
* show_name 显示名，方便阅读
* description 附加描述

### ac_rule_resource_privilege
角色、资源、权限 关联表。一个角色可以关联多个资源及资源相对应的权限，或者关联多个与资源无关的权限
* id
* id_rule
* id_resource
* id_privilege
* description 附加描述

### ac_sys_admin_rule
系统管理员的用户与角色关联表
* id
* id_admin
* id_rule

### ac_enterprise_admin_rule
企业管理员的用户与角色关联表
* id
* id_admin
* id_rule

### sys_admin
* id_admin
* name
* email
* mobile
* salt
* password
* real_name
* state
* wx_unionid
* wx_openid
* registe_date
* token 用于跨站点统一登录，无此需求可以忽略
* token_create_time token 创建时间

### enterprise_admin
* id_admin
* id_enterprise 所属企业
* name
* email
* mobile
* salt
* password
* real_name
* state
* wx_unionid
* wx_openid
* registe_date
* token 用于跨站点统一登录，无此需求可以忽略
* token_create_time token 创建时间

### enterprise
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


### website
* id_website 主键：网站 id
* id_enterprise 归属的企业 id
* name 网站名
* url 网站 url
* symbol_name 在整个系统中的唯一符号名，
* style 网站风格（皮肤和式样），默认为 0
* description
* state 状态，默认为 0
* registe_date 登记日期


### user_'网站的 id_website'
每个网站有一个
最终用户表，这个表在企业添加 website 时由后台系统自动创建
* id_user
* name
* email
* mobile
* salt
* password
* real_name
* state
* wx_unionid
* wx_openid
* registe_date
* token 用于跨站点统一登录，无此需求可以忽略
* token_create_time token 创建时间



### ac_user_rule_'网站的 id_website'
每个网站有一个
最终用户的用户与角色关联表，这个表在企业添加 website 时由后台系统自动创建
* id
* id_user 在 ac_user_'网站的 id_website' 表中的 id_user
* id_rule



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
* m=sys_admin_login
* 入参：用户名，口令
2. 企业管理员登录入口
* m=enterprise_admin_login
* 入参：企业符号名，用户名，口令
3. 最终用户登录入口
* m=user_login
* 入参：网站符号名，用户名，口令
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

## 最终用户归属
用户只能属于某个 web 站点，所以，每个 web 站点需要有一套：user_'网站的 id_website' 和 ac_user_rule_'网站的 id_website' 表。


# API
## sys_admin_login( args = ['email', 'password'] )
* 入参 args = ['email', 'password']
* 出参 user_info，内容为：['id_admin', 'name', 'type', ...]
    * 其中 type 为：0:sys, 1:enteprise, 2:websit
* 出参 user_privilege，内容为：['resource_privilege', 'privileges']
    * 其中 resource_privilege 为：资源权限数组； privileges 为权限数组。


# yyq_frame
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
