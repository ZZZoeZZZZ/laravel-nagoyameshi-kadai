<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Subscribed
{
    /**
     * 有料プランに登録済みであることを確認する
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 未登録の場合、有料プラン登録ページにリダイレクトする
        if(! $request->user()?->subscribed('premiun_plan')){
            return redirect('subscription/create');
        }
        
        return $next($request);
    }
}