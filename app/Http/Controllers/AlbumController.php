<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use Mail;
use App\Mail\NewAlbum;
use App\Models\Album;
use App\Models\Artist;
use App\Jobs\AnnounceNewAlbum;

class AlbumController extends Controller
{
    public function index()
    {
        $albums = DB::table('albums')
            ->join('artists', 'albums.artist_id', '=', 'artists.id')
            ->orderBy('artist')
            ->orderBy('title')
            ->get([
                'albums.id',
                'albums.title',
                'artists.name AS artist',
            ]);

        return view('album.index', [
            'albums' => $albums,
        ]);
    }

    public function create()
    {
        $artists = DB::table('artists')
            ->orderBy('name')
            ->get();

        return view('album.create', [
            'artists' => $artists,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:20',
            'artist' => 'required|exists:artists,id',
        ]);

        // DB::table('albums')->insert([
        //     'title' => $request->input('title'), // $_POST['title'] $_REQUEST['title']
        //     'artist_id' => $request->input('artist'),
        // ]);

        $album = new Album();
        $album->title = $request->input('title');
        $album->artist()->associate(Artist::find($request->input('artist')));
        $album->save();

        // Mail::to('dtang@usc.edu')->queue(new NewAlbum($album));
        AnnounceNewAlbum::dispatch($album);

        return redirect()
            ->route('album.index')
            ->with('success', "Successfully created album {$request->input('title')}");

    }

    public function edit($id)
    {
        $album = DB::table('albums')->where('id', '=', $id)->first();

        $artists = DB::table('artists')
            ->orderBy('name')
            ->get();

        return view('album.edit', [
            'album' => $album,
            'artists' => $artists,
        ]);
    }

    public function update($id, Request $request)
    {
        $request->validate([
            'title' => 'required|max:30',
            'artist' => 'required|exists:artists,id',
        ]);

        DB::table('albums')->where('id', '=', $id)->update([
            'title' => $request->input('title'), // $_POST['title'] $_REQUEST['title']
            'artist_id' => $request->input('artist'),
        ]);

        return redirect()
            ->route('album.index')
            ->with('success', "Successfully updated album {$request->input('title')}");
    }
}
