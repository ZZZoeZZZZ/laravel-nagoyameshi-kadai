<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NotSubscribed
{
    /**
     * 有料プラン未登録を確認
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 登録済みの場合は支払方法編集ページにリダイレクト
        if ($request->user()?->subscribed('premium_plan')) {
            return redirect('subscription/edit');
        }
        return $next($request);
    }
}
