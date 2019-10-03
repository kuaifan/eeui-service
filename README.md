# eeui-repair

eeui.app服务端（热更新、动态设置启动图等）代码。

## 目录结构

```text
├── zip                     // 修复包列表目录
│   ├── 12                  // ├── 应用版本号为12的修复包目录
│   │   ├── aa.json         // │   ├── 修复包参数配置文件
│   │   ├── aa.zip          // │   ├── 修复包zip文件
│   │   ├── ....json        // │   ├── ...
│   │   └── ....zip         // │   └── ...
│   └── ...                 // └── ...
├── config.json             // APP参数配置文件
└── index.php               // 服务访问入口文件
```

## config.json

> config.json APP参数配置文件:

```json
{
    "appkey": "test",
    "welcome_image": "",
    "welcome_wait": 2000,
    "welcome_skip": 1,
    "welcome_jump": "",
    "welcome_limit_s": 0,
    "welcome_limit_e": 0
}
```

> config.json APP参数配置说明:

| 属性名           | 类型     | 描述                          | 默认值     |
| ------------- | ------ | -------------------------- | ------- |
| appkey |`String`  | [配置文件](https://eeui.app/guide/config.html)中的appkey           | -       |
| welcome_image |`String`  | APP闪屏广告图片地址。例如：http://abc.com/welcome.png （留空不显闪屏广告）           | -       |
| welcome_wait |`Number`  | APP闪屏广告等待时间。时间单位：毫秒           | 2000       |
| welcome_skip |`Number`  | APP闪屏广告是否显示跳过等待。1：是、0：否           | 1       |
| welcome_jump |`String`  | APP闪屏广告点击打开的js页面路径。如：ad.js 或 https://abc.com/ad.js （留空点击无效）           | -       |
| welcome_limit_s |`Number`  | APP闪屏广告有效开始时限。单位：10位时间戳 （留空不限制）           | 0       |
| welcome_limit_e |`Number`  | APP闪屏广告有效结束时限。单位：10位时间戳 （留空不限制）           | 0       |


## zip修复包说明

```
修复包文件路径为：zip/`{应用版本号}`/`{fileName}`.zip
修复包配置文件路径为：zip/`{应用版本号}`/`{fileName}`.json
```

> json 修复包参数配置文件：

```json
{
    "platform": "android,ios",
    "debug": 0,
    "valid": 1,
    "reboot": 2,
    "reboot_info": {
        "title": "温馨提示",
        "message": "已为您更新至最新版本"
    },
    "clear_cache": 0
}
```

> json 修复包参数配置说明:

| 属性名           | 类型     | 描述                          | 默认值     |
| ------------- | ------ | -------------------------- | ------- |
| platform |`String`  | 更新平台。支持：`android`、`ios`           | android,ios       |
| debug |`Number`  | DEBUG版本可以收到此更新包。1：支持           | 0       |
| valid |`Number`  | 启用状态。1：启用、0：暂停、2：撤回           | 1       |
| reboot |`Number`  | 更新完成后。0：静默、1：自动重启、2：提示重启           | 2       |
| reboot_info.title |`String`  | 提示重启标题           | 提示标题       |
| reboot_info.message |`String`  | 提示重启内容           | 已为您更新至最新版本。       |
| clear_cache |`Number`  | 更新完成后。0：保留缓存、1：清除缓存           | 0       |
