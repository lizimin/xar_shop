<?php
/**
 * 支付宝支付
 */

return [
        //应用ID,您的APPID。
        'app_id' => "2016052401437504",

        //商户私钥, 请把生成的私钥文件中字符串拷贝在此
        'merchant_private_key' => "MIIEvgIBADANBgkqhkiG9w0BAQEFAASCBKgwggSkAgEAAoIBAQCk71vl0XD/2ftSJ+qdWbcr4rCq0Br8nmKyM1nJMezH4nxuHID7aBF4pOWgLUKBachM/RO1VOY7IYSudAhnvLo+WeXKZ9ctzZDhiezfrVWAe59/0+f7//ZeUJzr7KnonAdBKnDsrnG+8/uir+L4Rw+p0i4f0x+QlG6WEu5SVj2bf7xHAEUAig35PsbqnH52Kh55Pr99dWyIDzeQMgkveTtk3OoN01soAG4si1O7SSCfU8+Mq6SWilGmkEvz6tx9iFXPBGGUqHEx3SXC3F8H/K2Fu0pV+VmYPAs37tYf82V24cPE1JMjUMVkB7UuXIz+yQgy5tQBxoniCloYwcFrWpEzAgMBAAECggEASmfEwFeMr48pxnVFbPi1HnIkmtpI4l+dTKDHx3DjTYUJ9y6arU/UWeWhxXHFh9YtyzV8N5h2SISlc4Ha7NmB93DcrkPMGdibnHN5TarHYK/kU2lIRTHCdefN8syQFeSVjTtVOCC2JZuxkEHilXiRQ14S+r5mhfXAMamWo8ROBKCQflmjPcNslSnIKUupWRqpW+6MRunxLgQ75AgXtFEBIRDT6exGuuBciifdx0oesWQYvqiBlXffLCEL0h/IpmSmiqhkEmUvRpuJjhArtLqF1JrNeFQmpT94bKW4yJnxwV5od4uzSEH67M+9hHZ/ZXnw98ZDdVCoKn0KeyktIQ6uqQKBgQDb9uWcn1/BIjdAbQmNRowTkBiDazu314S5FVPfwvhmsQ1tQavzC4ZFIWBfXgJ4Gaw2oP+6WmSDANOjXCc97DTIFLNCyoBBIK7uZztgml+MBjFTPhKoz+fMwEQNYHfAWgVg4MvGci3y1k41gWKZZMVQJyUzs8sT1Q1LdFJc64UVrwKBgQC/9JQcXtswh2qbL0GCJX66YvjXzBqVwHPTbhQraW6Oh7hF79pWQVrG5AI/66M9QFnLyy5kxpc3PPQwPdGsu2olLX97d7RwSID3wwki5EGBnpEgHjd8oqCVZtMG5g+JIhblVCGBP1ayP8WDsRAwVdTe2U7Dl94YPTVDJefg5BIhvQKBgQCRBHKW0r/nba5tjDV67aLWFu8CXYUujCkVeMkmQb1QvrOyb1R01Qk9tGZ8GVeZZJuUHIrcilGvyLC/B7dbbMnTi0ov45+w0GJkDK0p4DzT7RVB4y+cGg2hgLSc+ReaOf9Hwoy2FXrTmZRQVC/0H2qykExHjOZ6+cBdGaBfYGsKQwKBgQCzc58TdspLcA2FzoPbe9ohvW0NsU4ZObYOrxZED2i/7rmjCDyB7s9CqN5Bi7UsCgDouKZCqDWt+ln+z4w5g2wUHZjUgHA7mEyZU8gyyllDKE5cTGNrLU4a3enixSk49pmZAzHfdqtCMMQh/WI5DcTYISe1S0DiQDaO89z3LcCVsQKBgHNMCVcrz47v5PC5m9wUtJ1baGJKGCj48X3052B//qM7Mt71NuwfniKfoQan9oRz7Pd+JfqUl2a4yT0EbiTdz+j1DAd/DlucIrsrWMsPcNkrRmkNflbu6p9ip/nDfcZpdUaYBiENwdP74aIeti8A0yxDuFK5/HGLOjxdPl/rXt58",

        //异步通知地址
        'notify_url' => "http://xcxcgj.gotomore.cn/notice.php",

        //同步跳转
        'return_url' => "http://xcxcgj.gotomore.cn/notice.php",

        //编码格式
        'charset' => "UTF-8",

        //签名方式
        'sign_type'=>"RSA2",

        //支付宝网关
        'gatewayUrl' => "https://openapi.alipay.com/gateway.do",

        //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
        'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA0dtaAS/3lujciKrMvfOcdiasaTuRmetMtMAORyQq4NNnIO2oUvB1otZGU7+8DwxP3TXMflL9u/OOT70h5K7xpbOYjYV5ABldeM1nWhytLxQF+rpvuPjwMAzrFq5vZPtgCmq0IBR9s96Tj4zmvlKy8IbTq0awc7aZNJGp6CQQ2im9f0q0yFhxhqwkMhK1AIcSaNuObOEaR+is0c16OkyeVKIro//vQc7+GAprhLjTvdClSZUdooPqA62QdHyFL8Xqu/9mI9ZSaZ/cKHO92prX4yf8ndU5ykMwIy/gFdU8UbshxO79EVXa+334X/E0r0Vfvc27YgqMwk0OV2ZB3X3XxQIDAQAB",
];
