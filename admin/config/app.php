<?php

// +----------------------------------------------------------------------
// | framework
// +----------------------------------------------------------------------
// | 版权所有 2014~2018 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://framework.thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

return [
    // 应用调试模式
    'app_debug'      => true,
    // 应用Trace调试
    'app_trace'      => false,
    // URL参数方式 0 按名称成对解析 1 按顺序解析
    'url_param_type' => 1,
    // 当前 ThinkAdmin 版本号
    'thinkadmin_ver' => 'v4',
	// 当前
    'web_url' => 'http://api.yunshanghulian.com/',
    'h5_url'=>'http://api.yunshanghulian.com/h5/#',
    
    //支付宝信息
    'gatewayUrl'            =>  'https://openapi.alipay.com/gateway.do',
    'appId'                 =>  '2021001144677653',
    //商户私钥
    'rsaPrivateKey'         =>  'MIIEowIBAAKCAQEAgqRn72kqR7wV2TaFHz7IcFar0aVICX0oAgT70cJPK7Mzy9BJxugaj15otQaJtEglztzgUqvXQ5uIH3DUWdlLE7bUaUJTiTfi9BDbhXtiKTA2MDXZ4K1/EYlbBYf+MZI4Fj6GLLwROOE2NIab77GqE/xDvbvuYiY8kCqJza8CFqeNiE/va1m/Ym2WUpKnghsdXccMpdtXCNbGy2PGgNTVtTOl2GS/ynBq6Hlzhm+EFoFRZaUsX74puJplPdnVegCbwqU0PCbhQuqi8CBQ6vEHzpC8iI5EGtC539hLdwmfadBAv8Oki9IkrYd3RXHpY2XHFPnXIoUBvyLsTxR+NZJpEwIDAQABAoIBADkRwsmGAk9F7bFurKaikYLpibNSZW6nutNvA+Z04IrxhJ3zRlday0d38Xuq+HrFzaZZPLFAmg/RoJxDeLArSS26f33f2Ign3r/JoWvlI1Nk2TckPyJ0B/9MJnP7HWLgQhoNhCWEnm1fjINTjkkeLnL+j0USFKfRlkFQI9SzL9FfC4xTqKAW7LduHcdinGLCPAcE510UgPkc9qBtKBCh0COlyrVEYMTmQntfqK2iRQaqeLvVdkWZ+53JwbFW0fBsx7nBn/IvLZqQTfeTg9J9ZR01up+d8+Lt2hmmwrLoV+8C+D9gb1CxBTyQ3DmNS0S1GqGYfftP3UzPCd1MBcYVgdkCgYEA3nouY4k7jWkmUgSP5VmWCwvJNDSXJa8k5RYPuFOpfMxcAFRl/e1/vjOXakK/hKcxIXzQcv8SU40VhFJnasfhAlmgIrX5VQXbiVQcCH9/GMHIuGE26fYqPvKfcDndr6Zx2SuRpIX3N+4wmO+qf3MPEXeZ8GdwZrgVM6deCgIlRIcCgYEAllPJh+pQFZIiVaXPARj7ZxjRMdp85LZKJefDV8egEaxwnSU34ySC8bTIiH9LksycAvbghs9lw+mauDS9YkUvjzGV14BWc0ijwXgp/NePhHTClLvF4SQtwRS4Vs+opt1tM4ZGhlJFNbe6lRi37YwBn5ouZLB1c2G45o0jXdqaZhUCgYBLMhLR08WMI1kvaZlVVLVRIHeuLIRV2V/oCk/f5m8n3k7OUbhzXj3KBNgNs2fWJ0iE4BH4fFwuX0ZBhjSsM8jKqY5ljQosAaHVRdd/y9AihwUXxMvxwiE/S3Q2U2ipgOGEHKTKbflELz6/wmnnT/Vs2vbt+ZhVL60C23P+gAEmqwKBgQCQCkC0puONh7S2dGXhG1ro8nKgXkYFsFVj9KrMVU8fICfXq23di4KcryjnAXIce0mR3ZROGDPegNvrXT2KB9kGT/DPyP0NAbFHFCjHSJUPygEYGsQEcnIU7BGlvNxQ5yJXmHXDBtmiGyYA7upuBxUXJNkHSb2AjUZqQxp8N5Zx8QKBgApSbL8q+xGPuOpvbRx85EKtSe0FA4+3sYUocpQAsmXuNapnmKgNxBFZlVOds8CQ2aaZQfm7efNmtpCthyDCUUv4hjWxCzffT78KogcKMo493KpmApatHdxXp5eyH0jT88zCbtEvbf/LFa2xC+CqYrU/5+aT+9SJ3ENB9Nb1lLeg',
    //支付宝公钥
    'alipayrsaPublicKey'=>
    'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqvPaskmrJLTnkwLrtphD/5sgouclYlW36cEDkX7g4nDA+psB4ZZxJ9lGWqN0l5PlObZlOJNLj4tuvrNQF04goT3lDPKMBn4z3COZdeZsddAAK/RghAjghT/5DIQqRUFVCuLaGyKH3XPX6Tz8a7QA2V9Y2iGF3uMVc+4Y7PdMZVvg8Vx0ZymWfSuXwtx6gCbx485fHygK99PaGMjhzOKsqpLYLCvqRznyMVUQR43iQ0C1peKFRNJslcw5zshCjJmPvopu9USLdK7/wcEelRbVSVG+FRI8CLizNQcQ1vLfdIQ6r9L/lHncFm/E47M/9BSt86wnlNJHpe9aPfzL6ailRQIDAQAB',
];
