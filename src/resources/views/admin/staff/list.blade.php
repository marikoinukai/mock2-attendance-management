@extends('layouts.app')

@section('title', '管理者 スタッフ一覧')

@section('content')
    <section class="staff-list">
        <h1 class="page-title">スタッフ一覧</h1>

        <table class="table staff-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>メールアドレス</th>
                    <th>月次勤怠</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($staffUsers as $staff)
                    <tr>
                        <td>{{ $staff->name }}</td>
                        <td>{{ $staff->email }}</td>
                        <td>
                            <a href="{{ route('admin.staff.attendance', $staff->id) }}">
                                詳細
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3">スタッフはいません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
@endsection
