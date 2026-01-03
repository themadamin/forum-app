<?php

namespace App\Http\Controllers;

use App\Models\Like;
use App\Policies\LikePolicy;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;

class LikeController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request, string $type, int $id)
    {
        $likeable = $this->findLikeable($type, $id);
        if ($request->user()->cannot('create', $likeable)){
            abort(403);
        }

        $likeable->likes()->create([
            'user_id' => $request->user()->id,
        ]);
        $likeable->increment('likes_count');

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $type, int $id)
    {
        $likeable = $this->findLikeable($type, $id);
        if ($request->user()->cannot('delete', $likeable)){
            abort(403);
        }

        $likeable->likes()->whereBelongsTo($request->user())->delete();
        $likeable->decrement('likes_count');

        return back();
    }

    protected function findLikeable(string $type, int $id): Model
    {
        /** @var class-string<Model>|null $modelName */
        $modelName = Relation::getMorphedModel($type);

        if ($modelName === null) {
            throw new ModelNotFoundException();
        }

        return $modelName::findOrFail($id);
    }
}
